<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * OTP types for verification workflows.
 */
enum OtpType: string
{
    case VERIFICATION = 'verification';
    case PASSWORD_RESET = 'password_reset';

    /**
     * Get the email subject for this OTP type.
     */
    public function emailSubject(): string
    {
        return match ($this) {
            self::VERIFICATION => 'Account Verification Code',
            self::PASSWORD_RESET => 'Password Reset Code',
        };
    }
}
