<?php

namespace App\Models;

use App\Enums\Availability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand',
        'model',
        'year',
        'type',
        'seats',
        'location',
        'price_per_hour',
        'image',
        'description',
        'features',
        'availability',
        'rating',
    ];

    protected $casts = [
        'features' => 'array',
        'year' => 'integer',
        'seats' => 'integer',
        'price_per_hour' => 'integer',
        'rating' => 'decimal:2',
        'availability' => Availability::class,
    ];

    // Relationships
    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    public function pricingTiers()
    {
        return $this->hasMany(CarPricingTier::class)->orderBy('from_hours');
    }

    public function bookings()
    {
        return $this->hasMany(CarBooking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'item_id')
            ->where('category', 'car')
            ->where('status', 'approved');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'item_id')
            ->where('category', 'car');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('availability', Availability::Available);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeBySeats($query, $seats)
    {
        return $query->where('seats', '>=', $seats);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price_per_hour', [$min, $max]);
    }

    public function scopeWithFeature($query, $feature)
    {
        return $query->whereJsonContains('features', $feature);
    }

    // Accessors
    public function getPricePerHourInEgpAttribute()
    {
        return $this->price_per_hour / 100; // Convert piasters to EGP
    }

    // Methods
    public function calculatePrice($hours)
    {
        $tiers = $this->pricingTiers;

        if ($tiers->isEmpty()) {
            return $this->price_per_hour * $hours;
        }

        $totalPrice = 0;
        $remainingHours = $hours;

        foreach ($tiers as $tier) {
            if ($remainingHours <= 0) break;

            $tierHours = $tier->to_hours
                ? min($remainingHours, $tier->to_hours - $tier->from_hours + 1)
                : $remainingHours;

            $totalPrice += $tierHours * $tier->price_per_hour;
            $remainingHours -= $tierHours;
        }

        return $totalPrice;
    }

    public function isAvailableForPeriod($pickupDatetime, $dropoffDatetime)
    {
        return !$this->bookings()
            ->where(function ($query) use ($pickupDatetime, $dropoffDatetime) {
                $query->whereBetween('pickup_datetime', [$pickupDatetime, $dropoffDatetime])
                    ->orWhereBetween('dropoff_datetime', [$pickupDatetime, $dropoffDatetime])
                    ->orWhere(function ($q) use ($pickupDatetime, $dropoffDatetime) {
                        $q->where('pickup_datetime', '<=', $pickupDatetime)
                            ->where('dropoff_datetime', '>=', $dropoffDatetime);
                    });
            })
            ->whereIn('status', ['confirmed', 'active'])
            ->exists();
    }

    public function updateRating()
    {
        $this->rating = $this->reviews()->avg('rating') ?? 0;
        $this->save();
    }
}
