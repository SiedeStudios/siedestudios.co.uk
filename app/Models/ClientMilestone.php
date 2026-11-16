<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'due_at', 'completed_at', 'sort_order'])]
class ClientMilestone extends Model
{
    protected $touches = ['project'];

    protected function casts(): array
    {
        return ['due_at' => 'date', 'completed_at' => 'datetime', 'sort_order' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'due_at' => $this->due_at?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
