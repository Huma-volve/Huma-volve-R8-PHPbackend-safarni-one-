<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OtpType;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OtpService $otpService
    ) {}

    /**
     * Get user profile.
     */
    public function getProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'profile_image' => $user->profile_image
                ? Storage::disk('public')->url($user->profile_image)
                : null,
            'role' => $user->role->value,
            'is_verified' => $user->is_verified,
            'created_at' => $user->created_at->toISOString(),
        ];
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): array
    {
        $updateData = [];
        $emailChanged = false;

        // Handle basic fields
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        if (isset($data['location'])) {
            $updateData['location'] = $data['location'];
        }

        // Handle email change - requires re-verification
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $updateData['email'] = $data['email'];
            $updateData['is_verified'] = false;
            $updateData['email_verified_at'] = null;
            $emailChanged = true;
        }

        // Handle profile image upload
        if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $path = $data['profile_image']->store('profile-images', 'public');
            $updateData['profile_image'] = $path;
        }

        // Update user
        $this->userRepository->update($user->id, $updateData);

        // Refresh user data
        $user->refresh();

        $response = [
            'success' => true,
            'message' => 'Profile updated successfully.',
            'profile' => $this->getProfile($user),
        ];

        // If email changed, send verification OTP
        if ($emailChanged) {
            $this->otpService->generate($user->email, OtpType::VERIFICATION);
            $response['message'] = 'Profile updated. Please verify your new email address.';
            $response['requires_verification'] = true;
        }

        return $response;
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.',
            ];
        }

        // Update password
        $this->userRepository->update($user->id, [
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens except current
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return [
            'success' => true,
            'message' => 'Password changed successfully.',
        ];
    }

    /**
     * Deactivate user account.
     */
    public function deactivateAccount(User $user): array
    {
        $this->userRepository->deactivate($user->id);

        // Revoke all tokens
        $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Account deactivated successfully.',
        ];
    }

    /**
     * Delete user account (soft delete).
     */
    public function deleteAccount(User $user): array
    {
        // Delete profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete the user
        $this->userRepository->softDelete($user->id);

        return [
            'success' => true,
            'message' => 'Account deleted successfully.',
        ];
    }
}
