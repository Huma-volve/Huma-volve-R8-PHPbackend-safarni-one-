<?php

declare(strict_types=1);

namespace App\Enums;

enum SeatClass: string
{
    case ECONOMY = 'economy';
    case BUSINESS = 'business';
    case FIRST = 'first';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ECONOMY => 'Economy Class',
            self::BUSINESS => 'Business Class',
            self::FIRST => 'First Class',
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