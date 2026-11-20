<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => ProjectCategory::ordered()->withCount('projects')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        ProjectCategory::create([...$data, 'slug' => UniqueSlug::for(ProjectCategory::class, $data['name'])]);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, ProjectCategory $category): RedirectResponse
    {
        $data = $this->validated($request);
        $category->update([...$data, 'slug' => UniqueSlug::for(ProjectCategory::class, $data['name'], $category->id)]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ProjectCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted. Its projects are now uncategorised.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]) + ['sort_order' => 0];
    }
}
