<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use App\Services\Monitoring\MonitorChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Monitors/Index', [
            'monitors' => Monitor::ordered()->get()->map(fn (Monitor $m) => [
                ...$m->only(['id', 'name', 'url', 'method', 'expected_status', 'keyword', 'timeout', 'is_public', 'is_paused', 'sort_order', 'last_response_ms', 'last_http_status', 'last_error']),
                'status' => $m->displayStatus(),
                'last_checked_at' => $m->last_checked_at?->toIso8601String(),
                'status_changed_at' => $m->status_changed_at?->toIso8601String(),
                'uptime_1' => $m->uptime(1),
                'uptime_30' => $m->uptime(30),
                'bars' => $m->dailyBars(30),
                'open_incident' => $m->incidents()->whereNull('resolved_at')->exists(),
            ]),
            'status_url' => route('status'),
            'suggestions' => collect([config('app.url'), 'https://dash.siedestudios.co.uk'])->filter()->unique()->values(),
        ]);
    }

    public function store(Request $request, MonitorChecker $checker): RedirectResponse
    {
        $monitor = Monitor::create([...$this->validated($request), 'sort_order' => (int) Monitor::max('sort_order') + 1]);
        $checker->run(collect([$monitor]));

        return back()->with('success', "{$monitor->name} added and checked.");
    }

    public function update(Request $request, Monitor $monitor): RedirectResponse
    {
        $monitor->update($this->validated($request, partial: true));

        return back()->with('success', 'Monitor saved.');
    }

    public function check(Monitor $monitor, MonitorChecker $checker): RedirectResponse
    {
        $checker->run(collect([$monitor]));
        $monitor->refresh();

        return back()->with($monitor->status === Monitor::DOWN || $monitor->consecutive_failures ? 'error' : 'success',
            $monitor->consecutive_failures ? "{$monitor->name}: {$monitor->last_error}" : "{$monitor->name} responded in {$monitor->last_response_ms} ms.");
    }

    public function destroy(Monitor $monitor): RedirectResponse
    {
        $monitor->delete();

        return back()->with('success', "{$monitor->name} removed with its history.");
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$req, 'string', 'max:80'],
            'url' => [$req, 'url:http,https', 'max:500'],
            'method' => ['sometimes', Rule::in(['GET', 'HEAD'])],
            'expected_status' => ['nullable', 'integer', 'between:100,599'],
            'keyword' => ['nullable', 'string', 'max:120'],
            'timeout' => ['sometimes', 'integer', 'between:2,30'],
            'is_public' => ['sometimes', 'boolean'],
            'is_paused' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}
