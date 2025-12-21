<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OtpType;
use App\Interfaces\Repositories\OtpRepositoryInterface;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected OtpRepositoryInterface $otpRepository
    ) {}

    /**
     * Generate and send a new OTP.
     */
    public function generate(string $email, OtpType $type): string
    {
        // Invalidate any existing OTPs for this email and type
        $this->otpRepository->invalidate($email, $type);

        // Generate a random 4-digit OTP
        $code = $this->generateCode();

        // Get expiry minutes from config
        $expiryMinutes = config('otp.expiry_minutes', 10);

        // Create the OTP record
        $this->otpRepository->create($email, $code, $type, $expiryMinutes);

        // Send the OTP email
        Mail::to($email)->send(new OtpMail($code, $type, $expiryMinutes));

        return $code;
    }

    /**
     * Verify an OTP.
     */
    public function verify(string $email, string $code, OtpType $type): bool
    {
        $otp = $this->otpRepository->findValid($email, $code, $type);

        if (!$otp) {
            return false;
        }

        // Mark the OTP as used
        $otp->markAsUsed();

        return true;
    }

    /**
     * Check if OTP can be resent (rate limiting).
     */
    public function canResend(string $email, OtpType $type): bool
    {
        $throttleSeconds = config('otp.resend_throttle_seconds', 60);

        return !$this->otpRepository->wasRecentlySent($email, $type, $throttleSeconds);
    }

    /**
     * Resend OTP with rate limiting check.
     */
    public function resend(string $email, OtpType $type): array
    {
        if (!$this->canResend($email, $type)) {
            $throttleSeconds = config('otp.resend_throttle_seconds', 60);

            return [
                'success' => false,
                'message' => "Please wait {$throttleSeconds} seconds before requesting a new OTP.",
            ];
        }

        $this->generate($email, $type);

        return [
            'success' => true,
            'message' => 'OTP sent successfully.',
        ];
    }

    /**
     * Generate a random OTP code.
     */
    protected function generateCode(): string
    {
        $length = config('otp.length', 4);
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('9', $length, '9');

        return (string) random_int($min, $max);
    }
}
