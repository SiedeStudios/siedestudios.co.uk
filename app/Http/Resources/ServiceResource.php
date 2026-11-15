<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'features' => $this->features ?? [],
            'starting_price' => $this->starting_price !== null ? (float) $this->starting_price : null,
            'price_note' => $this->price_note,
        ];
    }
}
