<?php

declare(strict_types=1);

namespace App\Enums;

enum PassengerTitle: string
{
    case MR = 'Mr';
    case MRS = 'Mrs';
    case MS = 'Ms';
    case DR = 'Dr';

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}