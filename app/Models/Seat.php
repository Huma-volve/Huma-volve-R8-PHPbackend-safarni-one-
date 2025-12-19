<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SeatClass;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
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
        'flight_id',
        'class',
        'row',
        'column',
        'is_available',
        'price_modifier_egp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'class' => SeatClass::class,
            'row' => 'integer',
            'is_available' => 'boolean',
            'price_modifier_egp' => 'integer',
        ];
    }

    /**
     * Get the flight this seat belongs to.
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get seat designation (e.g., "12A").
     */
    public function getDesignationAttribute(): string
    {
        return $this->row . $this->column;
    }
}