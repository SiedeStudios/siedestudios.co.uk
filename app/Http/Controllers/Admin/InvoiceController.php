<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $status = InvoiceStatus::tryFrom($request->string('status')->toString());

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => Invoice::query()
                ->when($status, fn ($q) => $q->where('status', $status))
                ->latest('id')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Invoice $i) => [
                    'id' => $i->id,
                    'reference' => $i->reference,
                    'client_name' => $i->client_name,
                    'client_company' => $i->client_company,
                    'total' => (float) $i->total,
                    'currency' => $i->currency,
                    'status' => $i->status->value,
                    'due_at' => $i->due_at?->toDateString(),
                    'paid_at' => $i->paid_at?->toIso8601String(),
                    'created_at' => $i->created_at->toIso8601String(),
                ]),
            'filter' => $status?->value,
            'counts' => Invoice::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(Request $request, Settings $settings): Response
    {
        $client = Client::find($request->integer('client'));

        return Inertia::render('Admin/Invoices/Form', [
            'invoice' => null,
            'currencies' => config('site.currencies'),
            'clients' => $this->clientOptions(),
            'defaults' => [
                'currency' => $settings->get('currency', 'GBP'),
                'client_id' => $client?->id,
                'client_project_id' => $client?->projects()->whereKey($request->integer('project'))->value('id'),
                'client_name' => $client?->name,
                'client_company' => $client?->company,
                'client_email' => $client?->email,
            ],
        ]);
    }

    private function clientOptions(): array
    {
        return Client::with('projects:id,client_id,title')->orderBy('name')->get(['id', 'name', 'company', 'email'])
            ->map(fn (Client $c) => [...$c->only(['id', 'name', 'company', 'email']), 'projects' => $c->projects->map->only(['id', 'title'])])
            ->all();
    }

    public function store(InvoiceRequest $request, Settings $settings): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request, $settings) {
            $invoice = new Invoice($request->safe()->except(['items', 'publish']));
            $invoice->reference = Invoice::generateReference($settings->get('invoice_prefix'));
            $invoice->status = $request->boolean('publish') ? InvoiceStatus::Open : InvoiceStatus::Draft;
            $invoice->issued_at ??= now();
            $invoice->save();

            $this->syncItems($invoice, $request->validated('items'));

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', "Invoice {$invoice->reference} created.");
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['items', 'ipnLogs']);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => [
                ...$invoice->toArray(),
                'status' => $invoice->status->value,
                'total' => (float) $invoice->total,
                'pay_url' => route('pay.show', $invoice),
                'items' => $invoice->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                ]),
            ],
            'statuses' => array_column(InvoiceStatus::cases(), 'value'),
        ]);
    }

    public function edit(Invoice $invoice): Response|RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid || $invoice->status === InvoiceStatus::Refunded) {
            return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Paid invoices cannot be edited.');
        }

        $invoice->load('items');

        return Inertia::render('Admin/Invoices/Form', [
            'invoice' => [
                ...$invoice->only(['id', 'reference', 'client_id', 'client_project_id', 'client_name', 'client_email', 'client_company', 'currency', 'notes']),
                'status' => $invoice->status->value,
                'issued_at' => $invoice->issued_at?->toDateString(),
                'due_at' => $invoice->due_at?->toDateString(),
                'items' => $invoice->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                ]),
            ],
            'currencies' => config('site.currencies'),
            'clients' => $this->clientOptions(),
            'defaults' => [],
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_if(in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Refunded], true), 403, 'Paid invoices cannot be edited.');

        DB::transaction(function () use ($request, $invoice) {
            $invoice->fill($request->safe()->except(['items', 'publish']));

            if ($request->boolean('publish') && $invoice->status === InvoiceStatus::Draft) {
                $invoice->status = InvoiceStatus::Open;
            }

            $invoice->save();
            $this->syncItems($invoice, $request->validated('items'));
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice saved.');
    }

    public function status(Request $request, Invoice $invoice): RedirectResponse
    {
        $status = InvoiceStatus::from($request->validate([
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
        ])['status']);

        $invoice->status = $status;

        if ($status === InvoiceStatus::Paid && ! $invoice->paid_at) {
            $invoice->paid_at = now();
            $invoice->paid_amount ??= $invoice->total;
        }

        $invoice->save();

        return back()->with('success', "Invoice marked as {$status->value}.");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('error', 'Paid invoices are kept for your records — void or refund them instead.');
        }

        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted.');
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach (array_values($items) as $i => $item) {
            $invoice->items()->create([...$item, 'sort_order' => $i]);
        }

        $invoice->unsetRelation('items');
        $invoice->recalculateTotal();
        $invoice->save();
    }
}
