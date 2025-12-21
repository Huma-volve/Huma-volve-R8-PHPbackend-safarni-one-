<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Get authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Update authenticated user's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        $response = [
            'success' => true,
            'message' => $result['message'],
            'data' => $result['profile'],
        ];

        if (isset($result['requires_verification'])) {
            $response['requires_verification'] = true;
        }

        return response()->json($response);
    }

    /**
     * Change authenticated user's password.
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $result = $this->profileService->changePassword(
            $request->user(),
            $request->input('current_password'),
            $request->input('password')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Deactivate authenticated user's account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deactivate(Request $request): JsonResponse
    {
        $result = $this->profileService->deactivateAccount($request->user());

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Delete authenticated user's account (soft delete).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $result = $this->profileService->deleteAccount($request->user());

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
