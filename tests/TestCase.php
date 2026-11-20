<?php

namespace Tests;

use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['site.admin_discord_ids' => ['111111111111111111']]);
    }

    protected function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'discord_id' => '111111111111111111',
            'discord_username' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function settings(array $values): void
    {
        app(Settings::class)->set($values);
    }
}
