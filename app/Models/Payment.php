<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',         // Minor units (Piasters)
        'currency',       // EGP 
        'gateway',        // stripe, paypal
        'transaction_id', // From Gateway
        'response_json',  // Full Gateway Response Log
        'status',         // pending, succeeded, failed
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
        ];
    }

    // Belongs to a Booking
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}