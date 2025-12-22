<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarPricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'from_hours',
        'to_hours',
        'price_per_hour',
    ];

    protected $casts = [
        'from_hours' => 'integer',
        'to_hours' => 'integer',
        'price_per_hour' => 'integer',
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // Accessors
    public function getPricePerHourInEgpAttribute()
    {
        return $this->price_per_hour / 100;
    }

    public function getDescriptionAttribute()
    {
        if ($this->to_hours) {
            return "Hours {$this->from_hours}-{$this->to_hours}";
        }
        return "Hours {$this->from_hours}+";
    }
}
