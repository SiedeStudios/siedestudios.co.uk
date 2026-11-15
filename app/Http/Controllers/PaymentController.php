<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PayPal\IpnListener;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function lookup(): Response
    {
        return Inertia::render('Pay/Lookup');
    }

    public function find(Request $request): RedirectResponse
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:32']]);
        $reference = Str::upper(trim($data['reference']));

        $invoice = Invoice::where('reference', $reference)->first();

        if (! $invoice || ! $invoice->status->isPublic()) {
            return back()->withErrors(['reference' => 'No invoice found with that ID. Check the reference on your invoice email.']);
        }

        return redirect()->route('pay.show', $invoice);
    }

    public function show(Request $request, Invoice $invoice, Settings $settings): Response
    {
        abort_unless($invoice->status->isPublic() || $request->user()?->is_admin, 404);

        $invoice->load('items');
        $paypalEmail = $settings->get('paypal_email');
        $sandbox = $settings->bool('paypal_sandbox');

        $checkout = $invoice->status->isPayable() && filled($paypalEmail) && (float) $invoice->total > 0 ? [
            'action' => $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr',
            'sandbox' => $sandbox,
            'fields' => [
                'cmd' => '_xclick',
                'business' => $paypalEmail,
                'item_name' => 'Invoice '.$invoice->reference.' — '.$settings->get('business_name', 'SiedeStudios'),
                'item_number' => $invoice->reference,
                'invoice' => $invoice->reference,
                'amount' => number_format((float) $invoice->total, 2, '.', ''),
                'currency_code' => $invoice->currency,
                'no_shipping' => '1',
                'no_note' => '1',
                'charset' => 'utf-8',
                'rm' => '1',
                'notify_url' => route('paypal.ipn'),
                'return' => route('pay.show', ['invoice' => $invoice, 'status' => 'success']),
                'cancel_return' => route('pay.show', ['invoice' => $invoice, 'status' => 'cancelled']),
            ],
        ] : null;

        return Inertia::render('Pay/Show', [
            'invoice' => [
                'reference' => $invoice->reference,
                'client_name' => $invoice->client_name,
                'client_company' => $invoice->client_company,
                'client_email' => $invoice->client_email,
                'currency' => $invoice->currency,
                'total' => (float) $invoice->total,
                'status' => $invoice->status->value,
                'notes' => $invoice->notes,
                'issued_at' => $invoice->issued_at?->toDateString(),
                'due_at' => $invoice->due_at?->toDateString(),
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'items' => $invoice->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                ]),
            ],
            'business' => [
                'name' => $settings->get('business_name'),
                'address' => $settings->get('business_address'),
                'footer' => $settings->get('invoice_footer'),
                'email' => $settings->get('contact_email'),
            ],
            'checkout' => $checkout,
            'returnStatus' => in_array($request->query('status'), ['success', 'cancelled'], true) ? $request->query('status') : null,
        ]);
    }
}
