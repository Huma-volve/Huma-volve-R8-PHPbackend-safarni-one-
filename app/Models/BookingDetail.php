<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDetail extends Model
{
    /** @use HasFactory<\Database\Factories\BookingDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'meta', // JSON payload for flight details, room info, etc.
    ];

    /**
     * Get the attributes that should be cast.
     * Important so we can access $detail->meta['seat_number'] directly
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    // Belongs back to the main Booking
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}