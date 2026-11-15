<?php

namespace App\Models;

use App\Enums\TestimonialStatus;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'project_id', 'token', 'name', 'company', 'role', 'avatar', 'body', 'rating',
    'status', 'is_featured', 'sort_order', 'submitted_at', 'approved_at',
])]
class Testimonial extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Testimonial $testimonial) {
            $testimonial->token ??= Str::random(40);
        });
    }

    protected function casts(): array
    {
        return [
            'status' => TestimonialStatus::class,
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', TestimonialStatus::Approved);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => Media::url($this->avatar));
    }

    /**
     * Public-facing shape — never includes the invite token.
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'role' => $this->role,
            'avatar' => $this->avatar_url,
            'body' => $this->body,
            'rating' => $this->rating,
            'project' => $this->relationLoaded('project') && $this->project
                ? ['title' => $this->project->title, 'slug' => $this->project->slug]
                : null,
        ];
    }
}
