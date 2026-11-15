<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\TestimonialStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\IpnLog;
use App\Models\Message;
use App\Models\Project;
use App\Models\Share;
use App\Models\Testimonial;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Settings $settings): Response
    {
        $currency = $settings->get('currency', 'GBP');
        $start = now()->startOfMonth()->subMonths(11);

        // Bucket paid invoices per month in PHP so the query stays database-agnostic.
        $paid = Invoice::where('status', InvoiceStatus::Paid)
            ->where('currency', $currency)
            ->where('paid_at', '>=', $start)
            ->get(['paid_at', 'paid_amount', 'total']);

        $revenue = collect(range(0, 11))->map(function (int $i) use ($start, $paid) {
            $month = $start->copy()->addMonths($i);

            return [
                'label' => $month->format('M'),
                'month' => $month->format('Y-m'),
                'total' => round($paid->filter(fn ($inv) => $inv->paid_at->format('Y-m') === $month->format('Y-m'))
                    ->sum(fn ($inv) => (float) ($inv->paid_amount ?? $inv->total)), 2),
            ];
        });

        $open = Invoice::where('status', InvoiceStatus::Open)->where('currency', $currency);

        return Inertia::render('Admin/Dashboard', [
            'currency' => $currency,
            'stats' => [
                'revenue_year' => round((float) Invoice::where('status', InvoiceStatus::Paid)->where('currency', $currency)->whereYear('paid_at', now()->year)->sum('paid_amount'), 2),
                'outstanding' => round((float) (clone $open)->sum('total'), 2),
                'outstanding_count' => (clone $open)->count(),
                'unread' => Message::unread()->count(),
                'pending_testimonials' => Testimonial::where('status', TestimonialStatus::Pending)->count(),
                'projects' => Project::where('status', ProjectStatus::Published)->count(),
                'drafts' => Project::where('status', ProjectStatus::Draft)->count(),
                'shares' => Share::count(),
            ],
            'revenue' => $revenue,
            'messages' => Message::latest()->limit(5)->get(['id', 'name', 'service_name', 'body', 'read_at', 'created_at']),
            'pending' => Testimonial::where('status', TestimonialStatus::Pending)->latest('submitted_at')->limit(4)
                ->get(['id', 'name', 'company', 'rating', 'body', 'submitted_at']),
            'invoices' => Invoice::latest()->limit(5)->get(['id', 'reference', 'client_name', 'total', 'currency', 'status', 'created_at']),
            'payments' => IpnLog::with('invoice:id,reference')->latest()->limit(5)
                ->get(['id', 'invoice_id', 'txn_id', 'payment_status', 'verification', 'outcome', 'mc_gross', 'mc_currency', 'created_at']),
            'checklist' => [
                'paypal' => filled($settings->get('paypal_email')),
                'webhook' => filled($settings->get('discord_webhook_url')),
                'logo' => filled($settings->get('logo')),
                'favicon' => filled($settings->get('favicon')),
            ],
        ]);
    }
}
