<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PassengerTitle;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Passenger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'title',
        'first_name',
        'last_name',
        'date_of_birth',
        'passport_number',
        'passport_expiry',
        'nationality',
        'special_requests',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'passport_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => PassengerTitle::class,
            'date_of_birth' => 'date',
            'passport_expiry' => 'date',
        ];
    }

    /**
     * Encrypt passport number when setting.
     */
    protected function passportNumber(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn(string $value) => Crypt::encryptString($value),
        );
    }

    /**
     * Get the booking this passenger belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->title->value} {$this->first_name} {$this->last_name}";
    }
}