<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDiscordUser(string $id): void
    {
        $user = (new SocialiteUser)->map([
            'id' => $id,
            'nickname' => 'someone',
            'name' => 'Some One',
            'email' => 'someone@example.com',
            'avatar' => 'https://cdn.discordapp.com/avatars/x.png',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertInertia(fn ($page) => $page->component('Admin/Login'));
    }

    public function test_there_is_no_password_login_or_registration(): void
    {
        $this->post('/login', ['email' => 'a@b.c', 'password' => 'x'])->assertNotFound();
        $this->get('/register')->assertNotFound();
    }

    public function test_allow_listed_discord_account_can_sign_in(): void
    {
        $this->fakeDiscordUser('111111111111111111');

        $this->get('/auth/discord/callback?code=abc')->assertRedirect('/admin');

        $this->assertAuthenticated();
        $user = User::firstWhere('discord_id', '111111111111111111');
        $this->assertTrue($user->is_admin);
        $this->assertSame('someone', $user->discord_username);
    }

    public function test_unknown_discord_account_is_rejected(): void
    {
        $this->fakeDiscordUser('999999999999999999');

        $this->get('/auth/discord/callback?code=abc')->assertRedirect('/admin/login');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['discord_id' => '999999999999999999']);
    }

    public function test_ids_added_in_settings_are_allowed(): void
    {
        $this->settings(['admin_discord_ids' => "222222222222222222\n333333333333333333"]);
        $this->fakeDiscordUser('333333333333333333');

        $this->get('/auth/discord/callback?code=abc')->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_removing_an_id_revokes_access_immediately(): void
    {
        $user = User::create(['name' => 'Old', 'discord_id' => '444444444444444444', 'is_admin' => true]);

        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_admin_can_view_dashboard_and_log_out(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
        $this->actingAs($admin)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
