<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A message on a client project's timeline, from the studio or the client. */
#[Fillable(['user_id', 'body'])]
class ClientUpdate extends Model
{
    protected $touches = ['project'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
            'author' => $this->user ? [
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
                'is_studio' => (bool) $this->user->is_admin,
            ] : ['name' => 'SiedeStudios', 'avatar' => null, 'is_studio' => true],
        ];
    }
}
