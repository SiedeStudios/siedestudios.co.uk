<?php

namespace App\Http\Middleware;

use App\Enums\TestimonialStatus;
use App\Models\Message;
use App\Models\Testimonial;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly Settings $settings) {}

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'site' => fn () => $this->settings->public(),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->discord_username,
                    'avatar' => $user->avatar,
                    'is_admin' => $user->is_admin,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'admin' => fn () => $user?->is_admin && $request->routeIs('admin.*') ? [
                'unread_messages' => Message::unread()->count(),
                'pending_testimonials' => Testimonial::where('status', TestimonialStatus::Pending)->count(),
            ] : null,
        ];
    }
}
