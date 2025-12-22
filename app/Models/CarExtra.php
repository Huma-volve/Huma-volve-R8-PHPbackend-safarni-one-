<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pricing_type',
        'price',
        'is_available',
    ];

    protected $casts = [
        'price' => 'integer',
        'is_available' => 'boolean',
    ];

    // Relationships
    public function bookingExtras()
    {
        return $this->hasMany(BookingExtra::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByPricingType($query, $type)
    {
        return $query->where('pricing_type', $type);
    }

    // Accessors
    public function getPriceInEgpAttribute()
    {
        return $this->price / 100;
    }

    // Methods
    public function calculatePrice($days = 1)
    {
        if ($this->pricing_type === 'per_rental') {
            return $this->price;
        }

        // per_day
        return $this->price * $days;
    }
}
