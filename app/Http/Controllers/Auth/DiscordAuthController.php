<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class DiscordAuthController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Admin/Login', [
            'configured' => filled(config('services.discord.client_id')) && filled(config('services.discord.client_secret')),
        ]);
    }

    public function redirect(): SymfonyRedirect
    {
        return Socialite::driver('discord')->scopes(['identify'])->redirect();
    }

    public function callback(Request $request, Settings $settings): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Discord sign-in was cancelled.');
        }

        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (Throwable $e) {
            Log::warning('Discord OAuth failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->with('error', 'Could not verify your Discord account. Please try again.');
        }

        $discordId = (string) $discordUser->getId();

        if (! in_array($discordId, $settings->adminDiscordIds(), true)) {
            Log::notice('Rejected admin login attempt', ['discord_id' => $discordId]);

            return redirect()->route('login')->with('error', 'That Discord account is not authorised for the admin panel.');
        }

        $user = User::updateOrCreate(['discord_id' => $discordId], [
            'name' => $discordUser->getName() ?: $discordUser->getNickname() ?: 'Admin',
            'discord_username' => $discordUser->getNickname(),
            'email' => $discordUser->getEmail(),
            'avatar' => $discordUser->getAvatar(),
            'is_admin' => true,
            'last_login_at' => now(),
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
