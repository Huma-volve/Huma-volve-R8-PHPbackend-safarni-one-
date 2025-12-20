<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $basePriceEgp = $this->base_price_egp;
        $taxAmount = (int) ($basePriceEgp * ($this->tax_percentage / 100));
        $totalPrice = $basePriceEgp + $taxAmount;

        return [
            'id' => $this->id,
            'flight_number' => $this->flight_number,
            'airline' => new AirlineResource($this->whenLoaded('airline')),
            'aircraft' => $this->when($this->relationLoaded('aircraft') && $this->aircraft, [
                'id' => $this->aircraft?->id,
                'type' => $this->aircraft?->type,
                'total_seats' => $this->aircraft?->total_seats,
            ]),
            'origin' => new AirportResource($this->whenLoaded('originAirport')),
            'destination' => new AirportResource($this->whenLoaded('destinationAirport')),
            'schedule' => [
                'departure_time' => $this->departure_time?->toISOString(),
                'arrival_time' => $this->arrival_time?->toISOString(),
                'duration_minutes' => $this->duration_minutes,
                'duration_formatted' => $this->formatDuration($this->duration_minutes),
            ],
            'stops' => $this->stops,
            'layover_details' => $this->layover_details,
            'baggage_rules' => $this->baggage_rules,
            'is_refundable' => $this->is_refundable,
            'fare_conditions' => $this->fare_conditions,
            'pricing' => [
                'base_price_piasters' => $basePriceEgp,
                'tax_percentage' => (float) $this->tax_percentage,
                'tax_amount_piasters' => $taxAmount,
                'total_price_piasters' => $totalPrice,
                'formatted' => [
                    'base_price' => number_format($basePriceEgp / 100, 2) . ' EGP',
                    'tax_amount' => number_format($taxAmount / 100, 2) . ' EGP',
                    'total_price' => number_format($totalPrice / 100, 2) . ' EGP',
                ],
            ],
            'is_active' => $this->is_active,
            'available_seats' => $this->when(
                $this->relationLoaded('seats'),
                fn() => $this->seats->where('is_available', true)->count()
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Format duration in hours and minutes.
     */
    protected function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }

        return "{$mins}m";
    }
}