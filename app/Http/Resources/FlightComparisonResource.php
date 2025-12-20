<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightComparisonResource extends JsonResource
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
            'airline' => [
                'code' => $this->airline?->code,
                'name' => $this->airline?->name,
                'logo_url' => $this->airline?->logo_url,
            ],
            'route' => [
                'origin' => [
                    'code' => $this->originAirport?->code,
                    'city' => $this->originAirport?->city,
                ],
                'destination' => [
                    'code' => $this->destinationAirport?->code,
                    'city' => $this->destinationAirport?->city,
                ],
            ],
            'duration_minutes' => $this->duration_minutes,
            'duration_formatted' => $this->formatDuration($this->duration_minutes),
            'stops' => $this->stops,
            'baggage_rules' => $this->baggage_rules,
            'is_refundable' => $this->is_refundable,
            'total_price_formatted' => number_format($totalPrice / 100, 2) . ' EGP',
            'total_price_piasters' => $totalPrice,
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