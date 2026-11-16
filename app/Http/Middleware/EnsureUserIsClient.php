<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Portal pages: only users linked to a client account. Deleting the client revokes access. */
class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->client_id) {
            return $next($request);
        }

        if ($user?->is_admin) {
            return redirect()->route('admin.clients.index')->with('error', 'The portal is for clients — manage it from here.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('error', 'Your client access has ended. Get in touch if that\'s unexpected.');
    }
}
