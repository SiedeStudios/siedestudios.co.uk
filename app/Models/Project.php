<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\TestimonialStatus;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'project_category_id', 'title', 'slug', 'summary', 'body', 'results', 'chapter_images', 'client', 'role', 'year', 'tags',
    'cover_image', 'site_url', 'discord_url', 'github_url', 'store_url', 'video_url',
    'is_featured', 'status', 'sort_order', 'published_at',
])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'results' => 'array',
            'chapter_images' => 'array',
            'is_featured' => 'boolean',
            'status' => ProjectStatus::class,
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function approvedTestimonials(): HasMany
    {
        return $this->testimonials()->where('status', TestimonialStatus::Approved)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ProjectStatus::Published);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => Media::url($this->cover_image));
    }

    protected function bodyHtml(): Attribute
    {
        return Attribute::get(fn () => $this->body
            ? Str::markdown($this->body, ['html_input' => 'strip', 'allow_unsafe_links' => false])
            : null);
    }
}
