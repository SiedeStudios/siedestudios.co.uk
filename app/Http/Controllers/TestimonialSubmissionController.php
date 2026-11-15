<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use App\Services\DiscordNotifier;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialSubmissionController extends Controller
{
    public function show(string $token): Response
    {
        $testimonial = Testimonial::where('token', $token)->with('project:id,title')->firstOrFail();

        return Inertia::render('Testimonial/Submit', [
            'token' => $testimonial->token,
            'invite' => [
                'name' => $testimonial->name,
                'company' => $testimonial->company,
                'role' => $testimonial->role,
                'avatar' => $testimonial->avatar_url,
                'project' => $testimonial->project?->title,
            ],
            'submitted' => $testimonial->status !== TestimonialStatus::Invited,
        ]);
    }

    public function store(Request $request, string $token, DiscordNotifier $discord, Settings $settings): RedirectResponse
    {
        $testimonial = Testimonial::where('token', $token)->firstOrFail();

        if ($testimonial->status !== TestimonialStatus::Invited) {
            return back()->with('error', 'This testimonial has already been submitted. Thank you!');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:20', 'max:1500'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $testimonial->update([
            ...$data,
            'status' => TestimonialStatus::Pending,
            'submitted_at' => now(),
        ]);

        if ($settings->bool('notify_testimonials')) {
            $discord->testimonialSubmitted($testimonial);
        }

        return back()->with('success', 'Thank you — your testimonial has been sent for review.');
    }
}
