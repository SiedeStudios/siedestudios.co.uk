<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Services/Index', [
            'services' => Service::ordered()->withCount('messages')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Services/Form', ['service' => null, 'icons' => Service::ICONS]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Service::create([
            ...$data,
            'slug' => UniqueSlug::for(Service::class, $data['slug'] ?? $data['title']),
            'sort_order' => (int) Service::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('Admin/Services/Form', ['service' => $service, 'icons' => Service::ICONS]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();
        $service->update([...$data, 'slug' => UniqueSlug::for(Service::class, $data['slug'] ?? $data['title'], $service->id)]);

        return redirect()->route('admin.services.index')->with('success', 'Service saved.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return back()->with('success', 'Service deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(fn () => collect($ids)->each(fn ($id, $i) => Service::whereKey($id)->update(['sort_order' => $i])));

        return back();
    }
}
