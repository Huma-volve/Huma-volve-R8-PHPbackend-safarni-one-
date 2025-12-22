<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of cars with filters
     * GET /api/cars
     */
    public function index(Request $request)
    {
        $query = Car::query()->with(['images' => fn($q) => $q->active()->ordered()]);

        // Filter by location
        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        // Filter by brand
        if ($request->has('brand')) {
            $query->byBrand($request->brand);
        }

        // Filter by type (sedan, suv, luxury)
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by minimum seats
        if ($request->has('seats')) {
            $query->bySeats($request->seats);
        }

        // Filter by price range (in piasters)
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Filter by feature
        if ($request->has('feature')) {
            $query->withFeature($request->feature);
        }

        // Filter by availability
        if ($request->boolean('available_only', true)) {
            $query->available();
        }

        // Search by availability for specific dates
        if ($request->has('pickup_datetime') && $request->has('dropoff_datetime')) {
            $pickupDatetime = $request->pickup_datetime;
            $dropoffDatetime = $request->dropoff_datetime;

            $query->whereDoesntHave('bookings', function ($q) use ($pickupDatetime, $dropoffDatetime) {
                $q->where(function ($query) use ($pickupDatetime, $dropoffDatetime) {
                    $query->whereBetween('pickup_datetime', [$pickupDatetime, $dropoffDatetime])
                        ->orWhereBetween('dropoff_datetime', [$pickupDatetime, $dropoffDatetime])
                        ->orWhere(function ($q) use ($pickupDatetime, $dropoffDatetime) {
                            $q->where('pickup_datetime', '<=', $pickupDatetime)
                                ->where('dropoff_datetime', '>=', $dropoffDatetime);
                        });
                })
                    ->whereIn('status', ['confirmed', 'active']);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'price') {
            $query->orderBy('price_per_hour', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->orderBy('rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 20);
        $cars = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cars,
        ]);
    }

    /**
     * Display the specified car with full details
     * GET /api/cars/{id}
     */
    public function show($id)
    {
        $car = Car::with([
            'images' => fn($q) => $q->active()->ordered(),
            'pricingTiers',
            'reviews' => fn($q) => $q->approved()->latest()->limit(10),
            'reviews.user:id,name,profile_image',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $car,
        ]);
    }

    /**
     * Get available brands
     * GET /api/cars/brands
     */
    public function brands()
    {
        $brands = Car::select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Get available locations
     * GET /api/cars/locations
     */
    public function locations()
    {
        $locations = Car::select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    /**
     * Calculate price for specific duration
     * POST /api/cars/{id}/calculate-price
     */
    public function calculatePrice(Request $request, $id)
    {
        $request->validate([
            'hours' => 'required|integer|min:1',
        ]);

        $car = Car::with('pricingTiers')->findOrFail($id);
        $totalPrice = $car->calculatePrice($request->hours);

        return response()->json([
            'success' => true,
            'data' => [
                'car_id' => $car->id,
                'hours' => $request->hours,
                'total_price' => $totalPrice,
                'total_price_egp' => $totalPrice / 100,
                'price_breakdown' => $this->getPriceBreakdown($car, $request->hours),
            ],
        ]);
    }

    /**
     * Check availability for specific period
     * POST /api/cars/{id}/check-availability
     */
    public function checkAvailability(Request $request, $id)
    {
        $request->validate([
            'pickup_datetime' => 'required|date|after:now',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
        ]);

        $car = Car::findOrFail($id);
        $isAvailable = $car->isAvailableForPeriod(
            $request->pickup_datetime,
            $request->dropoff_datetime
        );

        return response()->json([
            'success' => true,
            'data' => [
                'car_id' => $car->id,
                'is_available' => $isAvailable,
                'pickup_datetime' => $request->pickup_datetime,
                'dropoff_datetime' => $request->dropoff_datetime,
            ],
        ]);
    }

    /**
     * Helper: Get price breakdown by tiers
     */
    private function getPriceBreakdown($car, $hours)
    {
        $tiers = $car->pricingTiers;

        if ($tiers->isEmpty()) {
            return [[
                'tier' => 'Standard',
                'hours' => $hours,
                'price_per_hour' => $car->price_per_hour,
                'subtotal' => $car->price_per_hour * $hours,
            ]];
        }

        $breakdown = [];
        $remainingHours = $hours;

        foreach ($tiers as $tier) {
            if ($remainingHours <= 0) break;

            $tierHours = $tier->to_hours
                ? min($remainingHours, $tier->to_hours - $tier->from_hours + 1)
                : $remainingHours;

            $breakdown[] = [
                'tier' => $tier->description,
                'hours' => $tierHours,
                'price_per_hour' => $tier->price_per_hour,
                'price_per_hour_egp' => $tier->price_per_hour / 100,
                'subtotal' => $tierHours * $tier->price_per_hour,
                'subtotal_egp' => ($tierHours * $tier->price_per_hour) / 100,
            ];

            $remainingHours -= $tierHours;
        }

        return $breakdown;
    }
}
