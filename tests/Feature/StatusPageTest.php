<?php

namespace Tests\Feature;

use App\Models\Monitor;
use App\Models\MonitorDay;
use App\Models\MonitorIncident;
use App\Services\Monitoring\MonitorChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StatusPageTest extends TestCase
{
    use RefreshDatabase;

    private const HOOK = 'https://discord.com/api/webhooks/1/abc';

    private function monitor(array $attrs = []): Monitor
    {
        $monitor = new Monitor(['name' => 'Website', 'url' => 'https://site.test/']);
        $monitor->forceFill($attrs)->save(); // status isn't mass-assignable

        return $monitor;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer(); // run the deferred Discord webhook calls immediately
    }

    private function check(Monitor $monitor): Monitor
    {
        app(MonitorChecker::class)->run(collect([$monitor->fresh()]));

        return $monitor->fresh();
    }

    public function test_a_healthy_site_is_up_and_rolled_up_daily(): void
    {
        Http::fake(['site.test/*' => Http::response('<h1>Hello</h1>')]);
        $m = $this->check($this->monitor());

        $this->assertSame(Monitor::UP, $m->status);
        $this->assertSame(200, $m->last_http_status);
        $this->assertSame(1, MonitorDay::sole()->up_checks);
        Http::assertSent(fn (Request $r) => str_starts_with($r->header('User-Agent')[0] ?? '', 'SiedeStudios-Status'));
    }

    public function test_one_failure_is_not_an_outage_but_two_are(): void
    {
        $this->settings(['discord_webhook_url' => self::HOOK]);
        Http::fake(['site.test/*' => Http::response('', 502), 'discord.com/*' => Http::response()]);
        $m = $this->monitor(['status' => Monitor::UP]);

        $m = $this->check($m);
        $this->assertSame(Monitor::UP, $m->status, 'a single blip does not alert');
        $this->assertSame(0, MonitorIncident::count());

        $m = $this->check($m);
        $this->assertSame(Monitor::DOWN, $m->status);
        $this->assertSame('HTTP 502', MonitorIncident::sole()->reason);

        $this->check($m); // still down: no second incident or alert
        $this->assertSame(1, MonitorIncident::count());

        Http::assertSentCount(4); // 3 checks + 1 alert
        Http::assertSent(fn (Request $r) => $r->url() === self::HOOK && str_contains($r['embeds'][0]['title'], 'is down'));
    }

    public function test_recovery_resolves_the_incident(): void
    {
        $this->settings(['discord_webhook_url' => self::HOOK]);
        Http::fake([
            'site.test/*' => Http::sequence()->push('', 500)->push('', 500)->push('ok', 200),
            'discord.com/*' => Http::response(),
        ]);
        $m = $this->monitor(['status' => Monitor::UP]);

        $this->check($m);
        $this->check($m);
        $m = $this->check($m);

        $this->assertSame(Monitor::UP, $m->status);
        $this->assertNotNull(MonitorIncident::sole()->resolved_at);
        Http::assertSent(fn (Request $r) => $r->url() === self::HOOK && str_contains($r['embeds'][0]['title'], 'back up'));
    }

    public function test_keyword_and_expected_status_checks(): void
    {
        Http::fake(['site.test/*' => Http::response('Database error', 200)]);
        $this->check($this->monitor(['keyword' => 'Welcome']));
        $this->assertSame('Expected text not found', Monitor::sole()->last_error);
        $this->assertSame(1, Monitor::sole()->consecutive_failures);

        Monitor::query()->delete();
        Http::fake(['site.test/*' => Http::response('', 301)]);
        $this->assertSame(Monitor::UP, $this->check($this->monitor())->status, 'redirects count as up by default');
    }

    public function test_connection_failures_are_reported_plainly(): void
    {
        Http::fake(['site.test/*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10001 milliseconds')]);
        $this->assertSame('Timed out', $this->check($this->monitor())->last_error);
    }

    public function test_status_page(): void
    {
        $this->get('/status')->assertNotFound();

        $up = $this->monitor(['name' => 'Website', 'status' => Monitor::UP]);
        $this->monitor(['name' => 'Dash', 'url' => 'https://dash.test', 'status' => Monitor::DOWN]);
        $this->monitor(['name' => 'Private', 'url' => 'https://private.test', 'is_public' => false, 'status' => Monitor::DOWN]);
        MonitorDay::create(['monitor_id' => $up->id, 'date' => today(), 'checks' => 100, 'up_checks' => 99, 'total_ms' => 9900]);
        MonitorIncident::create(['monitor_id' => $up->id, 'started_at' => now()->subHours(2), 'resolved_at' => now()->subHour(), 'reason' => 'HTTP 503']);

        $this->get('/status')->assertOk()->assertInertia(fn ($p) => $p
            ->component('Status')
            ->where('overall', 'degraded')
            ->has('monitors', 2)
            ->where('monitors.0.name', 'Dash')
            ->where('monitors.1.uptime_90', 99)
            ->has('monitors.1.bars', 90)
            ->where('monitors.1.bars.89.uptime', 99)
            ->where('incidents.0.duration', 3600)
            ->where('incidents.0.reason', 'HTTP 503'));

        $this->get('/')->assertInertia(fn ($p) => $p->where('nav.status', true));
    }

    public function test_admin_manages_monitors(): void
    {
        Http::fake(['*' => Http::response('ok')]);
        $this->actingAs($this->admin());

        $this->get('/admin/monitors')->assertOk();
        $this->post('/admin/monitors', ['name' => 'Bad', 'url' => 'ftp://site.test'])->assertSessionHasErrors('url');
        $this->post('/admin/monitors', ['name' => 'Site', 'url' => 'https://site.test'])->assertSessionHas('success');
        $m = Monitor::sole();
        $this->assertSame(Monitor::UP, $m->status, 'checked immediately on creation');

        $this->patch("/admin/monitors/{$m->id}", ['is_paused' => true]);
        $this->assertTrue($m->fresh()->is_paused);
        $this->artisan('monitors:check')->assertSuccessful();
        $this->assertSame(1, $m->checks()->count(), 'paused monitors are skipped');

        $this->post("/admin/monitors/{$m->id}/check")->assertSessionHas('success');
        $this->delete("/admin/monitors/{$m->id}");
        $this->assertSame(0, Monitor::count());
    }
}
