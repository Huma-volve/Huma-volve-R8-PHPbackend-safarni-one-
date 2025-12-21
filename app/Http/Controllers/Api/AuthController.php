<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\OtpType;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['user'],
        ], 201);
    }

    /**
     * Verify OTP for email verification.
     *
     * @param VerifyOtpRequest $request
     * @return JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->input('email'),
            $request->input('code')
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
     * Login with email and password.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (!$result['success']) {
            $statusCode = isset($result['requires_verification']) ? 403 : 401;

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'requires_verification' => $result['requires_verification'] ?? false,
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'token' => $result['token'],
                'user' => $result['user'],
            ],
        ]);
    }

    /**
     * Redirect to Google OAuth.
     *
     * @return JsonResponse
     */
    public function googleRedirect(): JsonResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$redirectUri) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured.',
            ], 500);
        }

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'offline',
        ]);

        return response()->json([
            'success' => true,
            'url' => "https://accounts.google.com/o/oauth2/v2/auth?{$params}",
        ]);
    }

    /**
     * Handle Google OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function googleCallback(Request $request): JsonResponse
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization code is required.',
            ], 400);
        }

        try {
            // Exchange code for access token
            $tokenResponse = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if ($tokenResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authenticate with Google.',
                ], 401);
            }

            $accessToken = $tokenResponse->json('access_token');

            // Get user info
            $userResponse = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($userResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve user information from Google.',
                ], 401);
            }

            $googleUser = $userResponse->json();

            $result = $this->authService->socialLogin([
                'id' => $googleUser['id'],
                'email' => $googleUser['email'],
                'name' => $googleUser['name'] ?? $googleUser['email'],
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'token' => $result['token'],
                    'user' => $result['user'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed.',
            ], 500);
        }
    }

    /**
     * Initiate forgot password flow.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->forgotPassword($request->input('email'));

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Verify password reset OTP.
     *
     * @param VerifyOtpRequest $request
     * @return JsonResponse
     */
    public function verifyPasswordResetOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyPasswordResetOtp(
            $request->input('email'),
            $request->input('code')
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
            'data' => [
                'reset_token' => $result['reset_token'],
            ],
        ]);
    }

    /**
     * Reset password after OTP verification.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // First verify the OTP
        $verifyResult = $this->authService->verifyPasswordResetOtp(
            $request->input('email'),
            $request->input('code')
        );

        if (!$verifyResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $verifyResult['message'],
            ], 400);
        }

        // Then reset the password
        $result = $this->authService->resetPassword(
            $request->input('email'),
            $verifyResult['reset_token'],
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
     * Resend OTP.
     *
     * @param ResendOtpRequest $request
     * @return JsonResponse
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $type = OtpType::from($request->input('type'));

        $result = $this->authService->resendOtp(
            $request->input('email'),
            $type
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 429);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Logout user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
