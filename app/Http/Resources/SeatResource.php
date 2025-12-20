<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
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
            'flight_id' => $this->flight_id,
            'class' => $this->class->value ?? $this->class,
            'row' => $this->row,
            'column' => $this->column,
            'designation' => $this->row . $this->column,
            'is_available' => $this->is_available,
            'price_modifier' => [
                'piasters' => $this->price_modifier_egp,
                'formatted' => number_format($this->price_modifier_egp / 100, 2) . ' EGP',
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}