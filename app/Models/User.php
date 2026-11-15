<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'discord_id', 'discord_username', 'email', 'avatar', 'is_admin', 'last_login_at'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
