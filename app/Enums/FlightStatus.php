<?php

declare(strict_types=1);

namespace App\Enums;

enum FlightStatus: string
{
    case SCHEDULED = 'scheduled';
    case BOARDING = 'boarding';
    case DEPARTED = 'departed';
    case IN_AIR = 'in_air';
    case LANDED = 'landed';
    case CANCELLED = 'cancelled';
    case DELAYED = 'delayed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::BOARDING => 'Boarding',
            self::DEPARTED => 'Departed',
            self::IN_AIR => 'In Air',
            self::LANDED => 'Landed',
            self::CANCELLED => 'Cancelled',
            self::DELAYED => 'Delayed',
        };
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}