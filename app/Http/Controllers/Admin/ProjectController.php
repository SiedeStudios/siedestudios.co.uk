<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\Media;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => Project::with('category:id,name')
                ->withCount(['images', 'testimonials'])
                ->ordered()
                ->get()
                ->map(fn (Project $p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'status' => $p->status->value,
                    'is_featured' => $p->is_featured,
                    'category' => $p->category?->name,
                    'cover' => $p->cover_url,
                    'year' => $p->year,
                    'images_count' => $p->images_count,
                    'testimonials_count' => $p->testimonials_count,
                ]),
        ]);
    }

    public function create(): Response
    {
        return $this->form(new Project(['status' => ProjectStatus::Draft]));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = new Project();
        $this->fill($project, $request);
        $project->sort_order = (int) Project::max('sort_order') + 1;
        $project->save();

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Project created — now add some screenshots.');
    }

    public function edit(Project $project): Response
    {
        return $this->form($project->load('images'));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->fill($project, $request);
        $project->save();

        return back()->with('success', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->load('images');

        Media::delete($project->cover_image);
        $project->images->each(fn ($image) => Media::delete($image->path));
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(fn () => collect($ids)->each(fn ($id, $i) => Project::whereKey($id)->update(['sort_order' => $i])));

        return back()->with('success', 'Order updated.');
    }

    private function form(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => $project->exists ? [
                ...Arr::except($project->toArray(), ['images', 'category']),
                'status' => $project->status->value,
                'cover' => $project->cover_url,
                'images' => $project->images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url, 'caption' => $i->caption]),
            ] : null,
            'categories' => ProjectCategory::ordered()->get(['id', 'name']),
        ]);
    }

    private function fill(Project $project, ProjectRequest $request): void
    {
        $data = $request->safe()->except(['cover', 'remove_cover', 'slug']);
        $project->fill($data);
        $project->slug = UniqueSlug::for(Project::class, $request->input('slug') ?: $data['title'], $project->id);

        if ($project->status === ProjectStatus::Published && ! $project->published_at) {
            $project->published_at = now();
        }

        if ($request->boolean('remove_cover') || $request->hasFile('cover')) {
            Media::delete($project->getOriginal('cover_image'));
            $project->cover_image = null;
        }

        if ($request->hasFile('cover')) {
            $project->cover_image = $request->file('cover')->store('projects', 'public');
        }
    }
}
