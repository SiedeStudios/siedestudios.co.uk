<?php

namespace App\Services\PayPal;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\IpnLog;
use App\Services\DiscordNotifier;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Applies a PayPal IPN message to an invoice once PayPal has verified it,
 * following PayPal's recommended checks: payment status, unique txn_id,
 * receiver email, and amount/currency match.
 */
class IpnProcessor
{
    public function __construct(
        private readonly IpnListener $listener,
        private readonly Settings $settings,
        private readonly DiscordNotifier $discord,
    ) {
    }

    public function handle(string $rawBody, array $fields): IpnLog
    {
        $log = new IpnLog([
            'txn_id' => $fields['txn_id'] ?? null,
            'txn_type' => $fields['txn_type'] ?? null,
            'payment_status' => $fields['payment_status'] ?? null,
            'mc_gross' => $fields['mc_gross'] ?? null,
            'mc_currency' => $fields['mc_currency'] ?? null,
            'payer_email' => $fields['payer_email'] ?? null,
            'receiver_email' => $fields['receiver_email'] ?? ($fields['business'] ?? null),
            'payload' => $fields,
        ]);

        $reference = $fields['invoice'] ?? ($fields['item_number'] ?? null);
        $invoice = $reference ? Invoice::where('reference', $reference)->first() : null;
        $log->invoice_id = $invoice?->id;

        try {
            $verified = $this->listener
                ->useSandbox($this->settings->bool('paypal_sandbox'))
                ->timeout((int) config('services.paypal.ipn_verify_timeout', 30))
                ->verify($rawBody);
        } catch (IpnVerificationException $e) {
            Log::warning('PayPal IPN verification failed', ['error' => $e->getMessage(), 'txn_id' => $log->txn_id]);

            return $this->finish($log, 'ERROR', 'verification_failed', $e->getMessage());
        }

        if (! $verified) {
            return $this->finish($log, 'INVALID', 'rejected', 'PayPal reported this message as INVALID.');
        }

        if (! $invoice) {
            return $this->finish($log, 'VERIFIED', 'unknown_invoice', 'No invoice matches reference '.($reference ?? '(none)').'.');
        }

        $status = Str::lower($fields['payment_status'] ?? '');

        return match ($status) {
            'completed' => $this->applyPayment($log, $invoice, $fields),
            'refunded', 'reversed' => $this->applyRefund($log, $invoice, $fields),
            default => $this->finish($log, 'VERIFIED', 'ignored', 'Payment status "'.($fields['payment_status'] ?? 'unknown').'" recorded, no change made.'),
        };
    }

    private function applyPayment(IpnLog $log, Invoice $invoice, array $fields): IpnLog
    {
        $receiver = Str::lower(trim($fields['receiver_email'] ?? ($fields['business'] ?? '')));
        $expectedReceiver = Str::lower(trim((string) $this->settings->get('paypal_email')));

        if ($expectedReceiver === '' || $receiver !== $expectedReceiver) {
            return $this->finish($log, 'VERIFIED', 'receiver_mismatch', "Receiver {$receiver} does not match the configured PayPal email.");
        }

        $txnId = $fields['txn_id'] ?? null;

        if (blank($txnId)) {
            return $this->finish($log, 'VERIFIED', 'missing_txn', 'Completed payment without a txn_id.');
        }

        return DB::transaction(function () use ($log, $invoice, $fields, $txnId) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();

            if (Invoice::where('paypal_txn_id', $txnId)->exists()) {
                return $this->finish($log, 'VERIFIED', 'duplicate', "Transaction {$txnId} was already processed.");
            }

            if (! $invoice->status->isPayable()) {
                return $this->finish($log, 'VERIFIED', 'not_payable', "Invoice is {$invoice->status->value}; payment recorded for manual review.");
            }

            $currency = Str::upper($fields['mc_currency'] ?? '');
            $gross = (string) ($fields['mc_gross'] ?? '0');

            if ($currency !== $invoice->currency || ! Money::equals($gross, $invoice->total)) {
                return $this->finish($log, 'VERIFIED', 'amount_mismatch', "Expected {$invoice->total} {$invoice->currency}, received {$gross} {$currency}.");
            }

            $invoice->forceFill([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
                'paypal_txn_id' => $txnId,
                'paid_amount' => $gross,
                'payer_email' => $fields['payer_email'] ?? null,
            ])->save();

            $result = $this->finish($log, 'VERIFIED', 'paid', 'Invoice marked as paid.');

            if ($this->settings->bool('notify_payments')) {
                $this->discord->paymentReceived($invoice);
            }

            return $result;
        });
    }

    private function applyRefund(IpnLog $log, Invoice $invoice, array $fields): IpnLog
    {
        $parent = $fields['parent_txn_id'] ?? null;

        if ($invoice->status === InvoiceStatus::Paid && $parent && $parent === $invoice->paypal_txn_id) {
            $invoice->forceFill(['status' => InvoiceStatus::Refunded])->save();

            return $this->finish($log, 'VERIFIED', 'refunded', 'Invoice marked as refunded ('.$fields['payment_status'].').');
        }

        return $this->finish($log, 'VERIFIED', 'ignored', 'Refund/reversal did not match the invoice\'s transaction.');
    }

    private function finish(IpnLog $log, string $verification, string $outcome, string $notes): IpnLog
    {
        $log->fill(compact('verification', 'outcome', 'notes'))->save();

        return $log;
    }
}
