<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\IpnLog;
use App\Services\PayPal\IpnListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalIpnTest extends TestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        $this->settings(['paypal_email' => 'Payments@SiedeStudios.co.uk', 'paypal_sandbox' => false, 'discord_webhook_url' => null]);

        $this->invoice = Invoice::create([
            'reference' => 'SS-2611-ABC123',
            'client_name' => 'Client',
            'currency' => 'GBP',
            'status' => InvoiceStatus::Open,
        ]);
        $this->invoice->items()->create(['description' => 'Work', 'quantity' => 2, 'unit_price' => 62.50]);
        $this->invoice->recalculateTotal();
        $this->invoice->save();
    }

    private function ipn(array $overrides = []): array
    {
        return array_merge([
            'txn_type' => 'web_accept',
            'payment_status' => 'Completed',
            'txn_id' => '8AB12345CD678901E',
            'receiver_email' => 'payments@siedestudios.co.uk',
            'mc_gross' => '125.00',
            'mc_currency' => 'GBP',
            'invoice' => 'SS-2611-ABC123',
            'item_number' => 'SS-2611-ABC123',
            'payer_email' => 'buyer@example.com',
        ], $overrides);
    }

    private function postIpn(array $fields)
    {
        return $this->call('POST', '/paypal/ipn', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], IpnListener::encode($fields));
    }

    public function test_verified_completed_payment_marks_invoice_paid(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn())->assertOk()->assertContent('');

        $this->invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $this->invoice->status);
        $this->assertSame('8AB12345CD678901E', $this->invoice->paypal_txn_id);
        $this->assertSame('buyer@example.com', $this->invoice->payer_email);
        $this->assertNotNull($this->invoice->paid_at);
        $this->assertDatabaseHas('ipn_logs', ['invoice_id' => $this->invoice->id, 'verification' => 'VERIFIED', 'outcome' => 'paid']);
    }

    public function test_postback_echoes_the_raw_message_to_paypal(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);
        $fields = $this->ipn(['memo' => 'Café & “quotes”']);

        $this->postIpn($fields);

        Http::assertSent(fn (Request $request) => $request->url() === IpnListener::LIVE_ENDPOINT
            && $request->body() === 'cmd=_notify-validate&'.IpnListener::encode($fields)
            && $request->hasHeader('User-Agent'));
    }

    public function test_sandbox_setting_uses_sandbox_endpoint(): void
    {
        $this->settings(['paypal_sandbox' => true]);
        Http::fake([IpnListener::SANDBOX_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn());

        Http::assertSent(fn (Request $request) => $request->url() === IpnListener::SANDBOX_ENDPOINT);
        $this->assertSame(InvoiceStatus::Paid, $this->invoice->fresh()->status);
    }

    public function test_invalid_message_is_logged_and_ignored(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('INVALID')]);

        $this->postIpn($this->ipn())->assertOk();

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['verification' => 'INVALID', 'outcome' => 'rejected']);
    }

    public function test_paypal_outage_is_logged_without_marking_paid(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('Service Unavailable', 503)]);

        $this->postIpn($this->ipn())->assertOk();

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['verification' => 'ERROR', 'outcome' => 'verification_failed']);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn(['mc_gross' => '1.00']));

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'amount_mismatch']);
    }

    public function test_currency_mismatch_is_rejected(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn(['mc_currency' => 'USD']));

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'amount_mismatch']);
    }

    public function test_receiver_mismatch_is_rejected(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn(['receiver_email' => 'attacker@example.com', 'business' => 'attacker@example.com']));

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'receiver_mismatch']);
    }

    public function test_duplicate_transaction_is_only_applied_once(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn());
        $this->postIpn($this->ipn());

        $this->assertSame(2, IpnLog::count());
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'duplicate']);
    }

    public function test_pending_payment_does_not_mark_paid(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn(['payment_status' => 'Pending', 'pending_reason' => 'echeck']));

        $this->assertSame(InvoiceStatus::Open, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'ignored']);
    }

    public function test_refund_of_paid_invoice_marks_it_refunded(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);
        $this->postIpn($this->ipn());

        $this->postIpn($this->ipn(['payment_status' => 'Refunded', 'txn_id' => 'REFUND123', 'parent_txn_id' => '8AB12345CD678901E', 'mc_gross' => '-125.00']));

        $this->assertSame(InvoiceStatus::Refunded, $this->invoice->fresh()->status);
    }

    public function test_draft_invoice_cannot_be_paid(): void
    {
        $this->invoice->update(['status' => InvoiceStatus::Draft]);
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->postIpn($this->ipn());

        $this->assertSame(InvoiceStatus::Draft, $this->invoice->fresh()->status);
        $this->assertDatabaseHas('ipn_logs', ['outcome' => 'not_payable']);
    }

    public function test_ipn_endpoint_does_not_require_csrf_token(): void
    {
        Http::fake([IpnListener::LIVE_ENDPOINT => Http::response('VERIFIED')]);

        $this->post('/paypal/ipn', $this->ipn())->assertOk();
    }
}
