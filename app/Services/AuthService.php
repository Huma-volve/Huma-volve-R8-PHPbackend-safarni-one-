<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OtpType;
use App\Enums\UserRole;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OtpService $otpService
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        // Create user with unverified status
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::USER->value,
            'is_verified' => false,
            'status' => 'active',
        ]);

        // Generate and send OTP
        $this->otpService->generate($user->email, OtpType::VERIFICATION);

        return [
            'success' => true,
            'message' => 'Registration successful. Please check your email for the verification code.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Verify user's email with OTP.
     */
    public function verifyOtp(string $email, string $code): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        if ($user->is_verified) {
            return [
                'success' => false,
                'message' => 'Email is already verified.',
            ];
        }

        $verified = $this->otpService->verify($email, $code, OtpType::VERIFICATION);

        if (!$verified) {
            return [
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ];
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        return [
            'success' => true,
            'message' => 'Email verified successfully.',
        ];
    }

    /**
     * Login with email and password.
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials.',
            ];
        }

        if (!$user->is_verified) {
            return [
                'success' => false,
                'message' => 'Please verify your email before logging in.',
                'requires_verification' => true,
            ];
        }

        if ($user->status === 'inactive') {
            return [
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ];
        }

        // Create Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->formatUserProfile($user),
        ];
    }

    /**
     * Login or register via Google OAuth.
     */
    public function socialLogin(array $googleUser): array
    {
        // Check if user exists by Google ID
        $user = $this->userRepository->findByGoogleId($googleUser['id']);

        if (!$user) {
            // Check if user exists by email
            $user = $this->userRepository->findByEmail($googleUser['email']);

            if ($user) {
                // Link Google account to existing user
                $this->userRepository->update($user->id, [
                    'google_id' => $googleUser['id'],
                ]);
            } else {
                // Create new user
                $user = $this->userRepository->create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['id'],
                    'password' => Hash::make(bin2hex(random_bytes(16))), // Random password
                    'role' => UserRole::USER->value,
                    'is_verified' => true, // Social login auto-verifies email
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
            }
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->formatUserProfile($user),
        ];
    }

    /**
     * Initiate forgot password flow.
     */
    public function forgotPassword(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            // Don't reveal if user exists for security
            return [
                'success' => true,
                'message' => 'If an account with that email exists, you will receive a password reset code.',
            ];
        }

        // Generate and send OTP
        $this->otpService->generate($email, OtpType::PASSWORD_RESET);

        return [
            'success' => true,
            'message' => 'If an account with that email exists, you will receive a password reset code.',
        ];
    }

    /**
     * Verify password reset OTP.
     */
    public function verifyPasswordResetOtp(string $email, string $code): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ];
        }

        $verified = $this->otpService->verify($email, $code, OtpType::PASSWORD_RESET);

        if (!$verified) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ];
        }

        // Generate a temporary reset token
        $resetToken = bin2hex(random_bytes(32));
        cache()->put("password_reset_{$email}", $resetToken, now()->addMinutes(10));

        return [
            'success' => true,
            'message' => 'Code verified. You may now reset your password.',
            'reset_token' => $resetToken,
        ];
    }

    /**
     * Reset password after OTP verification.
     */
    public function resetPassword(string $email, string $resetToken, string $password): array
    {
        $cachedToken = cache()->get("password_reset_{$email}");

        if (!$cachedToken || $cachedToken !== $resetToken) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset session.',
            ];
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        // Update password
        $this->userRepository->update($user->id, [
            'password' => Hash::make($password),
        ]);

        // Clear the reset token
        cache()->forget("password_reset_{$email}");

        // Revoke all existing tokens
        $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ];
    }

    /**
     * Resend OTP.
     */
    public function resendOtp(string $email, OtpType $type): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            // Don't reveal if user exists
            return [
                'success' => true,
                'message' => 'If an account with that email exists, a new code has been sent.',
            ];
        }

        return $this->otpService->resend($email, $type);
    }

    /**
     * Logout user.
     */
    public function logout(User $user): bool
    {
        // Revoke current access token
        $user->currentAccessToken()->delete();

        return true;
    }

    /**
     * Format user profile for API response.
     */
    protected function formatUserProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'profile_image' => $user->profile_image,
            'role' => $user->role->value,
            'is_verified' => $user->is_verified,
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}
