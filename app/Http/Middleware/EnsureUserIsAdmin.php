<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function __construct(private readonly Settings $settings)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Re-check the allowlist on every request so removing an ID revokes access immediately.
        if (! $user?->is_admin || ! in_array($user->discord_id, $this->settings->adminDiscordIds(), true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'That Discord account is not authorised for the admin panel.');
        }

        return $next($request);
    }
}
