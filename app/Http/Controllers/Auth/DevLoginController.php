<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Signs in as a demo admin without Discord. Only routed when APP_ENV=local
 * and DEV_LOGIN=true (see routes/web.php) — used by GitHub Codespaces.
 */
class DevLoginController extends Controller
{
    public function __invoke(Request $request, Settings $settings): RedirectResponse
    {
        abort_unless(app()->environment('local') && config('app.dev_login'), 404);

        $discordId = $settings->adminDiscordIds()[0] ?? abort(403, 'Set ADMIN_DISCORD_IDS first.');

        $user = User::updateOrCreate(['discord_id' => $discordId], [
            'name' => 'Demo Admin',
            'discord_username' => 'demo',
            'is_admin' => true,
            'last_login_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
