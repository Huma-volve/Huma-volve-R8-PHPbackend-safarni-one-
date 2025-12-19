<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category',
        'item_id',
        'total_price',
        'payment_status',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
            'payment_status' => PaymentStatus::class,
            'status' => BookingStatus::class,
        ];
    }

    /**
     * Get the user who made this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of this booking.
     */
    public function categoryData(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'key');
    }

    /**
     * Get booking details.
     */
    public function detail(): HasOne
    {
        return $this->hasOne(BookingDetail::class);
    }

    /**
     * Get payment records.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get passengers for this booking.
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Get formatted total price in EGP.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->total_price / 100, 2) . ' EGP';
    }
}