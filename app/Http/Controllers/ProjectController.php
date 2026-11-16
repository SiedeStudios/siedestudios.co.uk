<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Resources\ProjectCardResource;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\CaseStudy;
use App\Support\Media;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::published()->with('category:id,name,slug')->ordered()->get();

        $categories = ProjectCategory::ordered()
            ->withCount(['projects' => fn ($q) => $q->published()])
            ->get(['id', 'name', 'slug', 'description'])
            ->filter(fn ($c) => $c->projects_count > 0)
            ->values();

        return Inertia::render('Projects/Index', [
            'projects' => ProjectCardResource::collection($projects)->resolve(),
            'categories' => $categories,
            'active' => $request->string('category')->toString() ?: null,
        ]);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_if($project->status !== ProjectStatus::Published && ! $request->user()?->is_admin, 404);

        $project->load(['category:id,name,slug', 'images', 'approvedTestimonials']);

        $next = Project::published()
            ->whereKeyNot($project->id)
            ->ordered()
            ->get(['id', 'title', 'slug', 'cover_image', 'sort_order', 'published_at'])
            ->sortBy(fn ($p) => $p->sort_order <= $project->sort_order ? 1 : 0)
            ->first();

        return Inertia::render('Projects/Show', [
            'project' => [
                ...ProjectCardResource::make($project)->resolve(),
                'body' => $project->body_html,
                'story' => CaseStudy::build($project),
                'accent' => $project->accent_color,
                'role' => $project->role,
                'links' => array_filter([
                    'site' => $project->site_url,
                    'discord' => $project->discord_url,
                    'github' => $project->github_url,
                    'store' => $project->store_url,
                    'video' => $project->video_url,
                ]),
                'images' => $project->images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url, 'srcset' => Media::srcset($i->path), 'caption' => $i->caption]),
                'is_draft' => $project->status !== ProjectStatus::Published,
            ],
            'testimonials' => $project->approvedTestimonials->map->toPublicArray(),
            'next' => $next ? ['title' => $next->title, 'slug' => $next->slug, 'cover' => Media::small($next->cover_image) ?? $next->cover_url] : null,
        ]);
    }
}
