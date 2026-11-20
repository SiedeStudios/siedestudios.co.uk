<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpnLog;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class PaymentLogController extends Controller
{
    public function __invoke(Settings $settings): Response
    {
        return Inertia::render('Admin/Payments/Index', [
            'logs' => IpnLog::with('invoice:id,reference')->latest('id')->paginate(25)->through(fn (IpnLog $log) => [
                ...$log->toArray(),
                'invoice' => $log->invoice?->reference,
            ]),
            'ipn_url' => route('paypal.ipn'),
            'sandbox' => $settings->bool('paypal_sandbox'),
            'paypal_email' => $settings->get('paypal_email'),
        ]);
    }
}
