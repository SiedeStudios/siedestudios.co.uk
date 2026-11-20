<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A GmodStore product shown on the site, refreshed from the Pivity API. */
#[Fillable([
    'gmodstore_id', 'slug', 'name', 'tagline', 'image_url', 'background_url', 'price', 'original_price',
    'currency', 'views', 'rating', 'reviews_count', 'sales_count', 'sales_capped', 'is_listed',
    'is_visible', 'is_featured', 'sort_order', 'synced_at', 'sync_error',
])]
class StoreProduct extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'original_price' => 'integer',
            'views' => 'integer',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'sales_count' => 'integer',
            'sales_capped' => 'boolean',
            'is_listed' => 'boolean',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'synced_at' => 'datetime',
        ];
    }

    /** Products visitors can see: shown by the admin and still for sale on GmodStore. */
    public function scopeShowcase(Builder $query): void
    {
        $query->where('is_visible', true)->where('is_listed', true)
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name');
    }

    public function storeUrl(): string
    {
        return 'https://www.gmodstore.com/market/view/'.($this->slug ?: $this->gmodstore_id);
    }

    public function toPublicArray(bool $withSales = false): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'image' => $this->image_url,
            'background' => $this->background_url,
            'price' => $this->price,
            'original_price' => $this->original_price > $this->price ? $this->original_price : null,
            'currency' => $this->currency,
            'rating' => $this->reviews_count > 0 ? round($this->rating, 1) : null,
            'reviews' => $this->reviews_count,
            'sales' => $withSales ? $this->sales_count : null,
            'sales_capped' => $this->sales_capped,
            'featured' => $this->is_featured,
            'url' => $this->storeUrl(),
        ];
    }
}
