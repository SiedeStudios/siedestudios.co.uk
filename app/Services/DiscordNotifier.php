<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ClientProject;
use App\Models\ClientUpdate;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Monitor;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\Settings;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Posts admin alerts to a Discord channel webhook (configured in Settings).
 * Sent after the response so a slow webhook never delays the visitor.
 */
class DiscordNotifier
{
    private const CRIMSON = 0xDC143C;

    public function __construct(private readonly Settings $settings) {}

    public function newMessage(Message $message): void
    {
        $this->send('New enquiry from '.$message->name, Str::limit($message->body, 900), [
            ['name' => 'Service', 'value' => $message->service_name ?: 'Not specified', 'inline' => true],
            ['name' => 'Budget', 'value' => $message->budget ?: '—', 'inline' => true],
            ['name' => 'Email', 'value' => $message->email, 'inline' => true],
            ['name' => 'Discord', 'value' => $message->discord ?: '—', 'inline' => true],
        ], route('admin.messages.show', $message));
    }

    public function paymentReceived(Invoice $invoice): void
    {
        $this->send('Invoice '.$invoice->reference.' paid', Number::currency((float) $invoice->paid_amount, $invoice->currency).' from '.$invoice->client_name, [
            ['name' => 'Payer', 'value' => $invoice->payer_email ?: '—', 'inline' => true],
            ['name' => 'PayPal txn', 'value' => $invoice->paypal_txn_id ?: '—', 'inline' => true],
        ], route('admin.invoices.show', $invoice));
    }

    public function testimonialSubmitted(Testimonial $testimonial): void
    {
        $this->send('Testimonial from '.$testimonial->name.' awaiting review', Str::limit((string) $testimonial->body, 900), [
            ['name' => 'Rating', 'value' => str_repeat('★', (int) $testimonial->rating).str_repeat('☆', 5 - (int) $testimonial->rating), 'inline' => true],
            ['name' => 'Company', 'value' => $testimonial->company ?: '—', 'inline' => true],
        ], route('admin.testimonials.edit', $testimonial));
    }

    public function portalJoined(Client $client, User $user): void
    {
        if (! $this->settings->bool('notify_portal')) {
            return;
        }

        $this->send($user->name.' joined the client portal', 'Linked to **'.$client->displayName().'** via their invite link.', [
            ['name' => 'Discord', 'value' => $user->discord_username ?: $user->discord_id, 'inline' => true],
        ], route('admin.clients.show', $client));
    }

    public function portalMessage(ClientProject $project, ClientUpdate $update): void
    {
        if (! $this->settings->bool('notify_portal')) {
            return;
        }

        $this->send('New message on '.$project->title, Str::limit($update->body, 900), [
            ['name' => 'From', 'value' => $update->user?->name ?? 'Client', 'inline' => true],
            ['name' => 'Client', 'value' => $project->client->displayName(), 'inline' => true],
        ], route('admin.client-projects.show', $project));
    }

    public function portalUpload(Client $client, ClientFile $file): void
    {
        if (! $this->settings->bool('notify_portal')) {
            return;
        }

        $this->send($client->displayName().' uploaded a file', $file->name, [
            ['name' => 'Size', 'value' => Number::fileSize($file->size), 'inline' => true],
            ['name' => 'Project', 'value' => $file->project?->title ?? '—', 'inline' => true],
        ], $file->client_project_id ? route('admin.client-projects.show', $file->client_project_id) : route('admin.clients.show', $client));
    }

    public function monitorDown(Monitor $monitor): void
    {
        if (! $this->settings->bool('notify_monitors')) {
            return;
        }

        $this->send('🔴 '.$monitor->name.' is down', $monitor->last_error ?: 'No response.', [
            ['name' => 'URL', 'value' => $monitor->url, 'inline' => false],
        ], route('admin.monitors.index'), 0xEF4444);
    }

    public function monitorUp(Monitor $monitor, ?int $downForSeconds): void
    {
        if (! $this->settings->bool('notify_monitors')) {
            return;
        }

        $this->send('🟢 '.$monitor->name.' is back up', $downForSeconds !== null ? 'Down for '.CarbonInterval::seconds($downForSeconds)->cascade()->forHumans(short: true).'.' : 'Responding again.', [
            ['name' => 'Response', 'value' => $monitor->last_response_ms.' ms', 'inline' => true],
        ], route('admin.monitors.index'), 0x22C55E);
    }

    private function send(string $title, string $description, array $fields, string $url, int $color = self::CRIMSON): void
    {
        $webhook = $this->settings->get('discord_webhook_url');

        if (blank($webhook)) {
            return;
        }

        $payload = [
            'username' => $this->settings->get('site_name', 'SiedeStudios'),
            'embeds' => [[
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'color' => $color,
                'fields' => $fields,
                'timestamp' => now()->toIso8601String(),
            ]],
        ];

        defer(function () use ($webhook, $payload) {
            try {
                Http::connectTimeout(3)->timeout(5)->post($webhook, $payload)->throw();
            } catch (Throwable $e) {
                Log::warning('Discord webhook failed', ['error' => $e->getMessage()]);
            }
        });
    }
}
