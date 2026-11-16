<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'company', 'email', 'notes'])]
#[Hidden(['invite_token'])]
class Client extends Model
{
    public const INVITE_DAYS = 14;

    protected function casts(): array
    {
        return ['invite_expires_at' => 'datetime'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ClientProject::class)->latest('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ClientFile::class)->latest('id');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->latest('id');
    }

    /** A single-use sign-up link; a new one replaces any previous link. */
    public function issueInvite(): string
    {
        $this->forceFill([
            'invite_token' => Str::random(48),
            'invite_expires_at' => now()->addDays(self::INVITE_DAYS),
        ])->save();

        return $this->invite_token;
    }

    public function revokeInvite(): void
    {
        $this->forceFill(['invite_token' => null, 'invite_expires_at' => null])->save();
    }

    public static function findByInvite(?string $token): ?self
    {
        if (blank($token) || strlen($token) !== 48) {
            return null;
        }

        return static::where('invite_token', $token)->where('invite_expires_at', '>', now())->first();
    }

    public function inviteUrl(): ?string
    {
        return $this->invite_token && $this->invite_expires_at?->isFuture()
            ? route('portal.invite', $this->invite_token)
            : null;
    }

    public function displayName(): string
    {
        return $this->company ?: $this->name;
    }
}
