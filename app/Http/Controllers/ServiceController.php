<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Services', [
            'services' => ServiceResource::collection(Service::active()->ordered()->get())->resolve(),
            'testimonials' => Testimonial::approved()->with('project:id,title,slug')
                ->orderByDesc('is_featured')->orderBy('sort_order')->limit(3)->get()
                ->map->toPublicArray(),
        ]);
    }
}
