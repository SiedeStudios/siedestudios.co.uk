<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PortalAuthController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Portal/Login', [
            'configured' => filled(config('services.discord.client_id')) && filled(config('services.discord.client_secret')),
        ]);
    }

    public function invite(Request $request, string $token): Response
    {
        $client = Client::findByInvite($token);

        if ($client && Auth::check()) {
            // Accepting an invite always signs in fresh, as whoever owns the Discord account used.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($client) {
            $request->session()->put('portal_invite', $token);
        }

        return Inertia::render('Portal/Invite', [
            'client' => $client ? ['name' => $client->name, 'company' => $client->company] : null,
            'configured' => filled(config('services.discord.client_id')) && filled(config('services.discord.client_secret')),
        ]);
    }
}
