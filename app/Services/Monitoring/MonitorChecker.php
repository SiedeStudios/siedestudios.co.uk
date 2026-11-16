<?php

namespace App\Services\Monitoring;

use App\Models\Monitor;
use App\Models\MonitorCheck;
use App\Models\MonitorDay;
use App\Models\MonitorIncident;
use App\Services\DiscordNotifier;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Checks monitors concurrently and records results, incidents and alerts. */
class MonitorChecker
{
    public function __construct(private readonly DiscordNotifier $notifier) {}

    /** @param  Collection<int, Monitor>  $monitors */
    public function run(Collection $monitors): void
    {
        if ($monitors->isEmpty()) {
            return;
        }

        $timings = [];
        $responses = Http::pool(fn (Pool $pool) => $monitors->map(fn (Monitor $m) => $pool->as((string) $m->id)
            ->withHeaders(['User-Agent' => 'SiedeStudios-Status/1.0 (+uptime monitor)'])
            ->withOptions([
                'allow_redirects' => ['max' => 5],
                'on_stats' => function (TransferStats $stats) use ($m, &$timings) {
                    $timings[$m->id] = (int) round($stats->getTransferTime() * 1000);
                },
            ])
            ->connectTimeout(min(5, $m->timeout ?: 10))
            ->timeout($m->timeout ?: 10)
            ->send($m->method, $m->url))->all());

        foreach ($monitors as $monitor) {
            $result = $this->evaluate($monitor, $responses[(string) $monitor->id] ?? null, $timings[$monitor->id] ?? null);
            $this->record($monitor, ...$result);
        }
    }

    /** @return array{0: bool, 1: ?int, 2: ?int, 3: ?string} [up, ms, status, reason] */
    private function evaluate(Monitor $monitor, mixed $response, ?int $ms): array
    {
        if ($response instanceof ConnectionException || ! $response instanceof Response) {
            $message = $response instanceof Throwable ? $response->getMessage() : '';

            return [false, null, null, match (true) {
                str_contains($message, 'timed out'), str_contains($message, 'Operation timed out') => 'Timed out',
                str_contains($message, 'resolve host') => 'DNS lookup failed',
                str_contains($message, 'SSL'), str_contains($message, 'certificate') => 'SSL error',
                str_contains($message, 'refused') => 'Connection refused',
                default => 'Connection failed',
            }];
        }

        $status = $response->status();
        $ok = $monitor->expected_status ? $status === $monitor->expected_status : $status >= 200 && $status < 400;

        if (! $ok) {
            return [false, $ms, $status, "HTTP {$status}"];
        }

        if ($monitor->keyword && $monitor->method !== 'HEAD' && ! str_contains($response->body(), $monitor->keyword)) {
            return [false, $ms, $status, 'Expected text not found'];
        }

        return [true, $ms, $status, null];
    }

    private function record(Monitor $monitor, bool $up, ?int $ms, ?int $status, ?string $reason): void
    {
        $now = now();
        $previous = $monitor->status;

        DB::transaction(function () use ($monitor, $up, $ms, $status, $reason, $now, $previous) {
            MonitorCheck::create(['monitor_id' => $monitor->id, 'up' => $up, 'response_ms' => $ms, 'http_status' => $status, 'error' => $reason, 'checked_at' => $now]);

            // Atomic upsert-and-increment, safe if two runs ever overlap.
            $day = ['monitor_id' => $monitor->id, 'date' => $now->toDateString()];
            MonitorDay::query()->insertOrIgnore([...$day, 'checks' => 0, 'up_checks' => 0, 'total_ms' => 0]);
            MonitorDay::query()->where($day)->update([
                'checks' => DB::raw('checks + 1'),
                'up_checks' => DB::raw('up_checks + '.($up ? 1 : 0)),
                'total_ms' => DB::raw('total_ms + '.($up ? (int) $ms : 0)),
            ]);

            $failures = $up ? 0 : $monitor->consecutive_failures + 1;
            $next = $up ? Monitor::UP : ($failures >= Monitor::CONFIRM_FAILURES ? Monitor::DOWN : $previous);

            $monitor->forceFill([
                'consecutive_failures' => $failures,
                'last_checked_at' => $now,
                'last_response_ms' => $ms,
                'last_http_status' => $status,
                'last_error' => $reason ?? $monitor->last_error,
                'status' => $next,
                'status_changed_at' => $next !== $previous ? $now : $monitor->status_changed_at,
            ])->save();

            if ($next === Monitor::DOWN && $previous !== Monitor::DOWN) {
                $monitor->incidents()->create(['started_at' => $now->copy()->subMinutes(Monitor::CONFIRM_FAILURES - 1), 'reason' => $reason]);
            }

            if ($next === Monitor::UP && $previous === Monitor::DOWN) {
                $monitor->incidents()->whereNull('resolved_at')->update(['resolved_at' => $now]);
            }
        });

        if ($monitor->status === Monitor::DOWN && $previous !== Monitor::DOWN) {
            $this->notifier->monitorDown($monitor);
        } elseif ($monitor->status === Monitor::UP && $previous === Monitor::DOWN) {
            $incident = MonitorIncident::where('monitor_id', $monitor->id)->latest('resolved_at')->first();
            $this->notifier->monitorUp($monitor, $incident?->durationSeconds());
        }
    }
}
