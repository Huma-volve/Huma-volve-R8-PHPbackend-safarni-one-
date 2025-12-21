<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Tour.
 *
 * @mixin \App\Models\Tour
 */
class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'location' => $this->location,
            'price' => $this->formatPrice($this->price),
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'rating' => (float) $this->rating,
            'thumbnail' => $this->thumbnail_url,
            'is_featured' => $this->is_featured,
        ];
    }

    /**
     * Format price as structured object.
     *
     * @param int $amountInPiasters Price in minor units (piasters)
     * @return array<string, mixed>
     */
    private function formatPrice(int $amountInPiasters): array
    {
        $amountInPounds = $amountInPiasters / 100;

        return [
            'amount' => $amountInPiasters,
            'formatted' => number_format($amountInPounds, 2) . ' EGP',
            'currency' => 'EGP',
        ];
    }
}