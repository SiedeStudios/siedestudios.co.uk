<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['monitor_id', 'started_at', 'resolved_at', 'reason'])]
class MonitorIncident extends Model
{
    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    public function durationSeconds(): ?int
    {
        return $this->resolved_at ? (int) $this->started_at->diffInSeconds($this->resolved_at) : null;
    }
}
