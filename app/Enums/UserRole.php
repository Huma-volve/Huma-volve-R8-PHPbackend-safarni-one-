<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User roles for RBAC.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case GUEST = 'guest';

    /**
     * Get all role values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
