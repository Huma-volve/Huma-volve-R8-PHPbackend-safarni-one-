<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_booking_id',
        'car_extra_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    // Relationships
    public function carBooking()
    {
        return $this->belongsTo(CarBooking::class);
    }

    public function carExtra()
    {
        return $this->belongsTo(CarExtra::class);
    }

    // Accessors
    public function getPriceInEgpAttribute()
    {
        return $this->price / 100;
    }
}
