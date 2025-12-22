<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'car_id',
        'pickup_datetime',
        'dropoff_datetime',
        'total_hours',
        'pickup_location',
        'dropoff_location',
        'driver_age',
        'driver_license',
        'base_price',
        'extras_price',
        'total_price',
        'status',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'dropoff_datetime' => 'datetime',
        'total_hours' => 'integer',
        'driver_age' => 'integer',
        'base_price' => 'integer',
        'extras_price' => 'integer',
        'total_price' => 'integer',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function extras()
    {
        return $this->hasMany(BookingExtra::class);
    }

    // Accessors
    public function getBasePriceInEgpAttribute()
    {
        return $this->base_price / 100;
    }

    public function getExtrasPriceInEgpAttribute()
    {
        return $this->extras_price / 100;
    }

    public function getTotalPriceInEgpAttribute()
    {
        return $this->total_price / 100;
    }

    public function getDurationInDaysAttribute()
    {
        return ceil($this->total_hours / 24);
    }

    // Methods
    public function calculateTotalHours()
    {
        $diff = $this->pickup_datetime->diff($this->dropoff_datetime);
        return ($diff->days * 24) + $diff->h + ($diff->i > 0 ? 1 : 0);
    }

    public function calculatePricing()
    {
        // Calculate base price using car's tiered pricing
        $this->base_price = $this->car->calculatePrice($this->total_hours);

        // Calculate extras price
        $this->extras_price = $this->extras->sum('price');

        // Calculate total
        $this->total_price = $this->base_price + $this->extras_price;

        $this->save();
    }
}
