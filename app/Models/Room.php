<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'main_image',
        'price_per_night',
        'occupancy',
        'bed_type',
        'area',
        'bathrooms',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class, 'item_id')->where('category', 'hotels');
    }
}
