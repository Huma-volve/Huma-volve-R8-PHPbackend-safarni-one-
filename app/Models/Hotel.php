<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'latitude',
        'longitude',
        'rating',
        'main_image',
        'discount',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'discount' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HotelImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'item_id')->where('category', 'hotels');
    }
}
