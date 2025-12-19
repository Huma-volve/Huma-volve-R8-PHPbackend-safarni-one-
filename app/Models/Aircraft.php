<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aircraft extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'aircraft';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'total_seats',
        'seat_map_config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_seats' => 'integer',
            'seat_map_config' => 'array',
        ];
    }

    /**
     * Get flights using this aircraft.
     */
    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class, 'aircraft_id'); // this is for how many hours the aircraft is used
    }
}