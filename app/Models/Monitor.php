<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A URL checked every minute for the status page. */
#[Fillable(['name', 'url', 'method', 'expected_status', 'keyword', 'timeout', 'is_public', 'is_paused', 'sort_order'])]
class Monitor extends Model
{
    public const UP = 'up';

    public const DOWN = 'down';

    public const UNKNOWN = 'unknown';

    /** Failed checks in a row before a monitor counts as down (avoids alerting on one blip). */
    public const CONFIRM_FAILURES = 2;

    protected $attributes = [
        'method' => 'GET',
        'timeout' => 10,
        'is_public' => true,
        'is_paused' => false,
        'sort_order' => 0,
        'status' => self::UNKNOWN,
        'consecutive_failures' => 0,
    ];

    protected function casts(): array
    {
        return [
            'expected_status' => 'integer',
            'timeout' => 'integer',
            'is_public' => 'boolean',
            'is_paused' => 'boolean',
            'sort_order' => 'integer',
            'consecutive_failures' => 'integer',
            'last_checked_at' => 'datetime',
            'last_response_ms' => 'integer',
            'last_http_status' => 'integer',
            'status_changed_at' => 'datetime',
        ];
    }

    public function checks(): HasMany
    {
        return $this->hasMany(MonitorCheck::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(MonitorDay::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(MonitorIncident::class)->latest('started_at');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_paused', false);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function displayStatus(): string
    {
        return $this->is_paused ? 'paused' : $this->status;
    }

    /** Uptime % from the daily rollups, or null with no data. */
    public function uptime(int $days): ?float
    {
        $row = $this->days()->where('date', '>=', now()->subDays($days - 1)->toDateString())
            ->selectRaw('SUM(checks) as checks, SUM(up_checks) as up')->first();

        return $row && $row->checks ? round($row->up / $row->checks * 100, 2) : null;
    }

    /**
     * One entry per day for the last $days days: uptime % (null when there's no data).
     *
     * @return list<array{date: string, uptime: ?float, avg_ms: ?int}>
     */
    public function dailyBars(int $days = 90): array
    {
        $rows = $this->days()->where('date', '>=', now()->subDays($days - 1)->toDateString())->get()
            ->keyBy(fn (MonitorDay $d) => $d->date->toDateString());
        $incidentDays = $this->incidentsByDay($days);

        return collect(range($days - 1, 0))->map(function (int $ago) use ($rows, $incidentDays) {
            $date = now()->subDays($ago)->toDateString();
            $row = $rows->get($date);

            return [
                'date' => $date,
                'uptime' => $row && $row->checks ? round($row->up_checks / $row->checks * 100, 2) : null,
                'avg_ms' => $row && $row->up_checks ? (int) round($row->total_ms / $row->up_checks) : null,
                'incidents' => $incidentDays[$date] ?? 0,
            ];
        })->all();
    }

    private function incidentsByDay(int $days): array
    {
        $counts = [];
        $this->incidents()->where('started_at', '>=', now()->subDays($days - 1)->startOfDay())->get(['started_at'])
            ->each(function (MonitorIncident $i) use (&$counts) {
                $d = $i->started_at->toDateString();
                $counts[$d] = ($counts[$d] ?? 0) + 1;
            });

        return $counts;
    }

    /** Hourly average response time for the last 24 hours. */
    public function responseSeries(): array
    {
        $since = now()->subHours(23)->startOfHour();
        $checks = $this->checks()->where('checked_at', '>=', $since)->where('up', true)->get(['response_ms', 'checked_at']);

        return collect(range(23, 0))->map(function (int $ago) use ($checks) {
            $hour = now()->subHours($ago)->startOfHour();
            $inHour = $checks->filter(fn ($c) => $c->checked_at->between($hour, $hour->copy()->endOfHour()));

            return ['hour' => $hour->toIso8601String(), 'ms' => $inHour->isEmpty() ? null : (int) round($inHour->avg('response_ms'))];
        })->all();
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'response_ms' => $this->status === self::UP ? $this->last_response_ms : null,
            'since' => $this->status_changed_at?->toIso8601String(),
            'uptime_90' => $this->uptime(90),
            'uptime_30' => $this->uptime(30),
            'bars' => $this->dailyBars(90),
            'response' => $this->responseSeries(),
        ];
    }
}
