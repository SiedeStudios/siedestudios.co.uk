<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\BrandColor;
use App\Support\ImageOptimizer;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectImageController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:20'],
            'images.*' => ['image', 'max:8192'],
        ]);

        $order = (int) $project->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            $project->images()->create([
                'path' => ImageOptimizer::optimize($file->store("projects/{$project->id}", 'public')),
                'sort_order' => ++$order,
            ]);
        }

        if (! $project->cover_image && $first = $project->images()->first()) {
            $project->update(['cover_image' => $first->path]);
            if ($project->accent_auto) {
                $project->update(['accent_color' => BrandColor::fromImage($first->path)]);
            }
        }

        return back()->with('success', count($request->file('images')).' screenshot(s) uploaded.');
    }

    public function update(Request $request, ProjectImage $image): RedirectResponse
    {
        $image->update($request->validate(['caption' => ['nullable', 'string', 'max:160']]));

        return back()->with('success', 'Caption saved.');
    }

    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(fn () => collect($ids)->each(
            fn ($id, $i) => $project->images()->whereKey($id)->update(['sort_order' => $i])
        ));

        return back();
    }

    public function destroy(ProjectImage $image): RedirectResponse
    {
        $project = $image->project;

        if ($project && $project->cover_image === $image->path) {
            $project->update(['cover_image' => null]);
        }

        Media::delete($image->path);
        $image->delete();

        return back()->with('success', 'Screenshot removed.');
    }
}
