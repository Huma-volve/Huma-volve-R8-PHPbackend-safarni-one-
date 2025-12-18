<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',       // FK linking to categories.key
        'item_id',        // Polymorphic ID
        'total_price',    // Stored in minor units (piasters)
        'payment_status', // pending, paid, failed
        'status',         // pending, confirmed, cancelled
    ];

    // Belongs to a User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Belongs to a Category (Custom Key Link)
    public function categoryData(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'key');
    }

    // Has one detail record (metadata)
    public function detail(): HasOne
    {
        return $this->hasOne(BookingDetail::class);
    }

    // Has many payment attempts
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}