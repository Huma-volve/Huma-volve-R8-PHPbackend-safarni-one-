<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Enums\OtpType;
use App\Models\Otp;

interface OtpRepositoryInterface
{
    /**
     * Create a new OTP.
     */
    public function create(string $email, string $code, OtpType $type, int $expiryMinutes): Otp;

    /**
     * Find a valid OTP.
     */
    public function findValid(string $email, string $code, OtpType $type): ?Otp;

    /**
     * Invalidate all OTPs for an email and type.
     */
    public function invalidate(string $email, OtpType $type): int;

    /**
     * Get the latest OTP for an email and type.
     */
    public function getLatest(string $email, OtpType $type): ?Otp;

    /**
     * Check if an OTP was recently sent (for rate limiting).
     */
    public function wasRecentlySent(string $email, OtpType $type, int $seconds): bool;
}
