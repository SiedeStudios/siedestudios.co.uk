<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\DiscordNotifier;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'devLogin' => app()->environment('local') && config('app.dev_login'),
        ]);
    }

    public function redirect(Request $request): SymfonyRedirect
    {
        // Remember which door the visitor came through so errors land on the right page.
        $request->session()->put('auth_for', $request->query('for') === 'portal' ? 'portal' : 'admin');

        return Socialite::driver('discord')->scopes(['identify'])->redirect();
    }

    public function callback(Request $request, Settings $settings, DiscordNotifier $notifier): RedirectResponse
    {
        $for = $request->session()->pull('auth_for', 'admin');
        $failed = fn (string $message) => redirect()->route($for === 'portal' ? 'portal.login' : 'login')->with('error', $message);

        if ($request->has('error')) {
            return $failed('Discord sign-in was cancelled.');
        }

        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (Throwable $e) {
            Log::warning('Discord OAuth failed', ['error' => $e->getMessage()]);

            return $failed('Could not verify your Discord account. Please try again.');
        }

        $discordId = (string) $discordUser->getId();
        $profile = [
            'name' => $discordUser->getName() ?: $discordUser->getNickname() ?: 'Client',
            'discord_username' => $discordUser->getNickname(),
            'avatar' => $discordUser->getAvatar(),
            'last_login_at' => now(),
        ];

        if (! in_array($discordId, $settings->adminDiscordIds(), true)) {
            return $this->clientLogin($request, $discordId, $profile, $failed, $notifier);
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

    /** Clients: an existing linked account, or a pending single-use invite from the session. */
    private function clientLogin(Request $request, string $discordId, array $profile, \Closure $failed, DiscordNotifier $notifier): RedirectResponse
    {
        $user = User::firstWhere('discord_id', $discordId);
        $invite = Client::findByInvite($request->session()->pull('portal_invite'));

        if ($invite) {
            $user = DB::transaction(function () use ($user, $invite, $discordId, $profile) {
                $user ??= new User(['discord_id' => $discordId]);
                $user->fill([...$profile, 'client_id' => $invite->id, 'is_admin' => false])->save();
                $invite->revokeInvite();

                return $user;
            });
            $notifier->portalJoined($invite, $user);
        } elseif ($user?->client_id) {
            $user->update($profile);
        } else {
            Log::notice('Rejected Discord login', ['discord_id' => $discordId]);

            return $failed('That Discord account isn\'t linked to a client account. Use the invite link you were sent, or ask us for a new one.');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($request->input('to') === 'portal' ? 'portal.login' : 'home');
    }
}
