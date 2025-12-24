<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'item_id',
        'total_price',
        'payment_status',
        'status',
    ];

    protected $casts = [
        'total_price' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carBooking()
    {
        return $this->hasOne(CarBooking::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Scopes
    public function scopeForCar($query)
    {
        return $query->where('category', 'car');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
