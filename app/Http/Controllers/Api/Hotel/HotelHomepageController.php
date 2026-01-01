<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class HotelHomepageController extends Controller
{
    use ApiResponse;

    /**
     * Get hotel recommendations (top rated or discounted).
     */
    public function recommendations(): JsonResponse
    {
        // Logic: Get top rated hotels, or hotels with discounts
        $hotels = Hotel::orderByDesc('rating')
            ->orderByDesc('discount')
            ->take(5)
            ->get();

        // Map images to full URLs
        $hotels->transform(function ($hotel) {
            $hotel->main_image = $hotel->main_image ? url('storage/' . $hotel->main_image) : null;
            return $hotel;
        });

        return $this->successResponse($hotels);
    }

    /**
     * Get nearby hotels.
     */

    public function nearby(Request $request): JsonResponse
    {
        $user = auth()->user();

        // If user is logged in and has coordinates
        if ($user && $user->latitude && $user->longitude) {
            $lat = $user->latitude;
            $lon = $user->longitude;

            // Haversine Formula for distance in KM
            $hotels = Hotel::select('*')
                ->selectRaw(
                    '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                    [$lat, $lon, $lat]
                )
                ->orderBy('distance')
                ->take(5)
                ->get();
        } else {
            // Fallback: Random hotels
            $hotels = Hotel::inRandomOrder()
                ->take(5)
                ->get();
        }

        $hotels->transform(function ($hotel) {
            $hotel->main_image = $hotel->main_image ? url('storage/' . $hotel->main_image) : null;
            // Add distance to response if available
            if (isset($hotel->distance)) {
                $hotel->distance = round($hotel->distance, 1) . ' km';
            }
            return $hotel;
        });

        return $this->successResponse($hotels);
    }

    /**
     * Search hotels.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'nullable|string',
            'checkin' => 'nullable|date',
            'checkout' => 'nullable|date|after:checkin',
            'guests' => 'nullable|integer|min:1'
        ]);

        $query = Hotel::query();

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%')
                ->orWhere('address', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('guests')) {
            // Filter hotels that have at least one room fitting the guest count
            $query->whereHas('rooms', function ($q) use ($request) {
                $q->where('occupancy', '>=', $request->guests);
            });
        }

        // Date Availability Logic (Mocked - Assuming all available for now as no bookings table integration yet)
        // In real implementation: Filter out hotels fully booked for these dates.

        $hotels = $query->get();

        $hotels->transform(function ($hotel) {
            $hotel->main_image = $hotel->main_image ? url('storage/' . $hotel->main_image) : null;
            return $hotel;
        });

        return $this->successResponse($hotels);
    }
}
