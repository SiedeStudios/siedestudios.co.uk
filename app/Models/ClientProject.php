<?php

namespace App\Models;

use App\Enums\ClientProjectStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['client_id', 'project_id', 'title', 'summary', 'status', 'staging_url', 'started_at', 'due_at', 'completed_at'])]
class ClientProject extends Model
{
    protected static function booted(): void
    {
        static::saving(function (ClientProject $project) {
            if ($project->isDirty('status')) {
                $project->completed_at = $project->status === ClientProjectStatus::Completed ? ($project->completed_at ?? now()) : null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ClientProjectStatus::class,
            'started_at' => 'date',
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function caseStudy(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ClientMilestone::class)->orderBy('sort_order')->orderBy('id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ClientUpdate::class)->latest('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ClientFile::class)->latest('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('id');
    }

    /** Percentage of milestones done (100 once the project is completed). */
    public function progress(): int
    {
        if ($this->status === ClientProjectStatus::Completed) {
            return 100;
        }

        $milestones = $this->relationLoaded('milestones') ? $this->milestones : $this->milestones()->get(['id', 'completed_at']);

        return $milestones->isEmpty() ? 0 : (int) round($milestones->whereNotNull('completed_at')->count() / $milestones->count() * 100);
    }

    public function summaryArray(): array
    {
        $next = $this->milestones->whereNull('completed_at')->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'progress' => $this->progress(),
            'due_at' => $this->due_at?->toDateString(),
            'started_at' => $this->started_at?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'staging_url' => $this->staging_url,
            'milestones_total' => $this->milestones->count(),
            'milestones_done' => $this->milestones->whereNotNull('completed_at')->count(),
            'next_milestone' => $next ? ['title' => $next->title, 'due_at' => $next->due_at?->toDateString()] : null,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
