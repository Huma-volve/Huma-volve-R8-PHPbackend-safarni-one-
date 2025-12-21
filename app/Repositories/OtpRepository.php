<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\OtpType;
use App\Interfaces\Repositories\OtpRepositoryInterface;
use App\Models\Otp;

class OtpRepository implements OtpRepositoryInterface
{
    /**
     * Create a new OTP.
     */
    public function create(string $email, string $code, OtpType $type, int $expiryMinutes): Otp
    {
        return Otp::create([
            'email' => $email,
            'code' => $code,
            'type' => $type->value,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Find a valid OTP.
     */
    public function findValid(string $email, string $code, OtpType $type): ?Otp
    {
        return Otp::forEmail($email)
            ->ofType($type)
            ->where('code', $code)
            ->valid()
            ->first();
    }

    /**
     * Invalidate all OTPs for an email and type.
     */
    public function invalidate(string $email, OtpType $type): int
    {
        return Otp::forEmail($email)
            ->ofType($type)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }

    /**
     * Get the latest OTP for an email and type.
     */
    public function getLatest(string $email, OtpType $type): ?Otp
    {
        return Otp::forEmail($email)
            ->ofType($type)
            ->latest()
            ->first();
    }

    /**
     * Check if an OTP was recently sent (for rate limiting).
     */
    public function wasRecentlySent(string $email, OtpType $type, int $seconds): bool
    {
        return Otp::forEmail($email)
            ->ofType($type)
            ->where('created_at', '>', now()->subSeconds($seconds))
            ->exists();
    }
}
