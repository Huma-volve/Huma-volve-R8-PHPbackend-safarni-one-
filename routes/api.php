<?php

use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PassengerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]));

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    // Registration & Verification
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify', [AuthController::class, 'verifyOtp']);

    // Login
    Route::post('/login', [AuthController::class, 'login']);

    // Google OAuth
    Route::get('/google', [AuthController::class, 'googleRedirect']);
    Route::get('/google/callback', [AuthController::class, 'googleCallback']);

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-otp', [AuthController::class, 'verifyPasswordResetOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // OTP Resend (with rate limiting)
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
        ->middleware('throttle:otp-resend');
});

/*
|--------------------------------------------------------------------------
| Public Routes (Guest Access)
|--------------------------------------------------------------------------
*/

// Airports
Route::prefix('airports')->group(function () {
    Route::get('/', [AirportController::class, 'index']);
    Route::get('/code/{code}', [AirportController::class, 'findByCode']);
    Route::get('/{airport}', [AirportController::class, 'show']);
});

// Airlines
Route::prefix('airlines')->group(function () {
    Route::get('/', [AirlineController::class, 'index']);
    Route::get('/code/{code}', [AirlineController::class, 'findByCode']);
    Route::get('/{airline}', [AirlineController::class, 'show']);
});

// Flights
Route::prefix('flights')->group(function () {
    Route::get('/', [FlightController::class, 'index']);
    Route::get('/compare', [FlightController::class, 'compare']);
    Route::get('/{flight}', [FlightController::class, 'show']);
    Route::get('/{flight}/seats', [SeatController::class, 'index']);
});

// Seats
Route::prefix('seats')->group(function () {
    Route::get('/{seat}', [SeatController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Current User (legacy endpoint)
    Route::get('/user', fn(Request $request) => $request->user());

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);

        // These routes require email verification
        Route::middleware('verified')->group(function () {
            Route::put('/password', [ProfileController::class, 'changePassword']);
            Route::post('/deactivate', [ProfileController::class, 'deactivate']);
            Route::delete('/', [ProfileController::class, 'delete']);
        });
    });

    // Seat Locking
    Route::post('/seats/lock', [SeatController::class, 'lock']);
    Route::delete('/seats/{seat}/release', [SeatController::class, 'release']);

    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/summary', [BookingController::class, 'summary']);
        Route::post('/checkout', [BookingController::class, 'checkout']);
        Route::get('/{booking}', [BookingController::class, 'show']);
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel']);

        // Passengers within booking
        Route::get('/{booking}/passengers', [PassengerController::class, 'index']);
        Route::post('/{booking}/passengers', [PassengerController::class, 'store']);
    });

    // Passengers
    Route::prefix('passengers')->group(function () {
        Route::get('/{passenger}', [PassengerController::class, 'show']);
        Route::put('/{passenger}', [PassengerController::class, 'update']);
        Route::delete('/{passenger}', [PassengerController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Airports Management
    Route::post('/airports', [AirportController::class, 'store']);
    Route::put('/airports/{airport}', [AirportController::class, 'update']);
    Route::delete('/airports/{airport}', [AirportController::class, 'destroy']);

    // Airlines Management
    Route::post('/airlines', [AirlineController::class, 'store']);
    Route::put('/airlines/{airline}', [AirlineController::class, 'update']);
    Route::delete('/airlines/{airline}', [AirlineController::class, 'destroy']);

    // Flights Management
    Route::post('/flights', [FlightController::class, 'store']);
    Route::put('/flights/{flight}', [FlightController::class, 'update']);
    Route::delete('/flights/{flight}', [FlightController::class, 'destroy']);
});
