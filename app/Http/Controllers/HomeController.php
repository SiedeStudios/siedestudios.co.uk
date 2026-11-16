<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ServiceResource;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoreProduct;
use App\Models\Testimonial;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featured = Project::published()
            ->with('category:id,name,slug')
            ->orderByDesc('is_featured')
            ->ordered()
            ->limit(6)
            ->get();

        $testimonials = Testimonial::approved()
            ->with('project:id,title,slug')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'projects' => ProjectCardResource::collection($featured)->resolve(),
            'services' => ServiceResource::collection(Service::active()->ordered()->get())->resolve(),
            'testimonials' => $testimonials->map->toPublicArray(),
            'store' => StoreProduct::showcase()->limit(3)->get()->map->toPublicArray(app(Settings::class)->bool('gmodstore_show_sales')),
            'stats' => [
                'projects' => Project::published()->count(),
                'rating' => round((float) Testimonial::approved()->avg('rating'), 1),
                'reviews' => Testimonial::approved()->count(),
            ],
        ]);
    }
}
