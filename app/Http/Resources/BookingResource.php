<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'user_id' => $this->user_id,
            'category' => $this->category,
            'status' => $this->status->value ?? $this->status,
            'payment_status' => $this->payment_status->value ?? $this->payment_status,
            'pricing' => [
                'total_price_piasters' => $this->total_price,
                'formatted' => number_format($this->total_price / 100, 2) . ' EGP',
            ],
            'flight_details' => $this->when(
                $this->relationLoaded('detail') && $this->detail,
                fn() => $this->detail->meta
            ),
            'passengers' => PassengerResource::collection($this->whenLoaded('passengers')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}