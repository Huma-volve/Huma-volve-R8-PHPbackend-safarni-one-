<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'amount' => [
                'piasters' => $this->amount,
                'formatted' => number_format($this->amount / 100, 2) . ' ' . $this->currency,
            ],
            'currency' => $this->currency,
            'gateway' => $this->gateway,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status->value ?? $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}