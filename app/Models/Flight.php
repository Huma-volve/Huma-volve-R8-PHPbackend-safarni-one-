<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flight extends Model
{
    use HasFactory, HasUuids;

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'flight_number',
        'airline_id',
        'aircraft_id',
        'origin_airport_id',
        'destination_airport_id',
        'departure_time',
        'arrival_time',
        'duration_minutes',
        'stops',
        'layover_details',
        'baggage_rules',
        'is_refundable',
        'fare_conditions',
        'base_price_egp',
        'tax_percentage',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
            'duration_minutes' => 'integer',
            'stops' => 'integer',
            'layover_details' => 'array',
            'is_refundable' => 'boolean',
            'base_price_egp' => 'integer',
            'tax_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the airline operating this flight.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the aircraft used for this flight.
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    /**
     * Get the origin airport.
     */
    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }

    /**
     * Get the destination airport.
     */
    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }

    /**
     * Get all seats for this flight.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Calculate total price including tax in piasters.
     */
    public function getTotalPriceAttribute(): int
    {
        $taxAmount = (int) ($this->base_price_egp * ($this->tax_percentage / 100));
        return $this->base_price_egp + $taxAmount;
    }

    /**
     * Get price formatted in EGP.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->total_price / 100, 2) . ' EGP';
    }
}