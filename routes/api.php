<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CarComparisonController;
use App\Http\Controllers\Api\CarExtraController;
use App\Http\Controllers\Api\CarBookingController;
use App\Http\Controllers\Api\CarReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (Guest access)
Route::prefix('cars')->group(function () {
    // Car listing and search
    Route::get('/', [CarController::class, 'index']); // GET /api/cars?location=Cairo&brand=Toyota&seats=4
    Route::get('/brands', [CarController::class, 'brands']); // GET /api/cars/brands
    Route::get('/locations', [CarController::class, 'locations']); // GET /api/cars/locations
    Route::get('/{id}', [CarController::class, 'show']); // GET /api/cars/1

    // Price calculation
    Route::post('/{id}/calculate-price', [CarController::class, 'calculatePrice']); // POST /api/cars/1/calculate-price
    Route::post('/{id}/check-availability', [CarController::class, 'checkAvailability']); // POST /api/cars/1/check-availability

    // Car comparison
    Route::post('/compare', [CarComparisonController::class, 'compare']); // POST /api/cars/compare

    // Reviews (public read)
    Route::get('/{carId}/reviews', [CarReviewController::class, 'index']); // GET /api/cars/1/reviews
});

// Car extras (public)
Route::prefix('car-extras')->group(function () {
    Route::get('/', [CarExtraController::class, 'index']); // GET /api/car-extras
    Route::post('/{id}/calculate-price', [CarExtraController::class, 'calculatePrice']); // POST /api/car-extras/1/calculate-price
});

// Protected routes (Authenticated users only)
Route::middleware('auth:sanctum')->group(function () {

    // Car bookings
    Route::prefix('bookings/cars')->group(function () {
        Route::get('/', [CarBookingController::class, 'index']); // GET /api/bookings/cars
        Route::post('/', [CarBookingController::class, 'store']); // POST /api/bookings/cars
        Route::get('/{id}', [CarBookingController::class, 'show']); // GET /api/bookings/cars/1
        Route::post('/{id}/cancel', [CarBookingController::class, 'cancel']); // POST /api/bookings/cars/1/cancel
    });

    // Reviews (authenticated write)
    Route::prefix('cars/{carId}/reviews')->group(function () {
        Route::post('/', [CarReviewController::class, 'store']); // POST /api/cars/1/reviews
    });

    Route::prefix('reviews')->group(function () {
        Route::put('/{id}', [CarReviewController::class, 'update']); // PUT /api/reviews/1
        Route::delete('/{id}', [CarReviewController::class, 'destroy']); // DELETE /api/reviews/1
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Booking management
    Route::post('/bookings/cars/{id}/confirm', [CarBookingController::class, 'confirm']); // POST /api/admin/bookings/cars/1/confirm

    // TODO: Add admin CRUD for cars, extras, pricing tiers, review approval, etc.
});
