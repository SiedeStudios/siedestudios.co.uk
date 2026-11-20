<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'slug', 'title', 'content', 'language', 'password', 'burn_after_read', 'views', 'expires_at'])]
#[Hidden(['password'])]
class Share extends Model
{
    use MassPrunable;

    public const LANGUAGES = [
        'plaintext', 'lua', 'php', 'javascript', 'typescript', 'json', 'html', 'css', 'scss',
        'sql', 'bash', 'python', 'yaml', 'ini', 'markdown', 'csharp', 'cpp', 'xml', 'diff',
    ];

    protected static function booted(): void
    {
        static::creating(function (Share $share) {
            if (blank($share->slug)) {
                do {
                    $slug = Str::lower(Str::random(8));
                } while (static::where('slug', $slug)->exists());

                $share->slug = $slug;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'burn_after_read' => 'boolean',
            'views' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Expired shares are removed by the scheduled model:prune command.
     */
    public function prunable(): Builder
    {
        return static::query()->whereNotNull('expires_at')->where('expires_at', '<', now()->subDay());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isProtected(): bool
    {
        return filled($this->password);
    }
}
