<?php

namespace App\Http\Controllers;

use App\Models\Monitor;
use App\Models\MonitorIncident;
use Inertia\Inertia;
use Inertia\Response;

class StatusController extends Controller
{
    public function __invoke(): Response
    {
        $monitors = Monitor::where('is_public', true)->active()->ordered()->get();
        abort_if($monitors->isEmpty(), 404);

        $down = $monitors->where('status', Monitor::DOWN)->count();
        $checked = $monitors->where('status', '!=', Monitor::UNKNOWN);

        return Inertia::render('Status', [
            'overall' => match (true) {
                $checked->isEmpty() => 'unknown',
                $down === 0 => 'up',
                $down === $monitors->count() => 'down',
                default => 'degraded',
            },
            'monitors' => $monitors->map->toPublicArray(),
            'incidents' => MonitorIncident::whereIn('monitor_id', $monitors->pluck('id'))
                ->where('started_at', '>=', now()->subDays(30))
                ->with('monitor:id,name')
                ->latest('started_at')
                ->limit(20)
                ->get()
                ->map(fn (MonitorIncident $i) => [
                    'id' => $i->id,
                    'monitor' => $i->monitor->name,
                    'reason' => $i->reason,
                    'started_at' => $i->started_at->toIso8601String(),
                    'resolved_at' => $i->resolved_at?->toIso8601String(),
                    'duration' => $i->durationSeconds(),
                ]),
            'checked_at' => $monitors->max('last_checked_at')?->toIso8601String(),
        ]);
    }
}
