<?php

namespace App\Providers;

use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\RoleMiddleware;
use App\Interfaces\Repositories\OtpRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Interfaces\Repositories\TourRepositoryInterface;
use App\Repositories\OtpRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TourRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OtpRepositoryInterface::class, OtpRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TourRepositoryInterface::class, TourRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware aliases
        Route::aliasMiddleware('verified', EnsureEmailVerified::class);
        Route::aliasMiddleware('role', RoleMiddleware::class);

        // Configure rate limiters
        RateLimiter::for('otp-resend', function (Request $request) {
            $throttleSeconds = config('otp.resend_throttle_seconds', 60);

            return Limit::perMinutes(1, 1)
                ->by($request->input('email') ?? $request->ip())
                ->response(function () use ($throttleSeconds) {
                    return response()->json([
                        'success' => false,
                        'message' => "Please wait {$throttleSeconds} seconds before requesting a new OTP.",
                    ], 429);
                });
        });
    }
}
