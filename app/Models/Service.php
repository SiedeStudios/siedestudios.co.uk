<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'icon', 'tagline', 'description', 'features', 'starting_price', 'price_note', 'is_active', 'sort_order'])]
class Service extends Model
{
    public const ICONS = ['code', 'layers', 'gamepad', 'pen', 'server', 'bolt', 'cube', 'terminal', 'shield', 'chat'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'starting_price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
