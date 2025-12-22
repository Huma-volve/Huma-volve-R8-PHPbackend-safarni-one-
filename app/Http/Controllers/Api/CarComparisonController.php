<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarComparisonController extends Controller
{
    /**
     * Compare multiple cars side-by-side
     * POST /api/cars/compare
     */
    public function compare(Request $request)
    {
        $request->validate([
            'car_ids' => 'required|array|min:2|max:4',
            'car_ids.*' => 'required|exists:cars,id',
        ]);

        $cars = Car::with(['images' => fn($q) => $q->active()->ordered(), 'pricingTiers'])
            ->whereIn('id', $request->car_ids)
            ->get();

        $comparison = $cars->map(function ($car) {
            return [
                'id' => $car->id,
                'brand' => $car->brand,
                'model' => $car->model,
                'year' => $car->year,
                'type' => $car->type,
                'seats' => $car->seats,
                'location' => $car->location,
                'image' => $car->image,
                'rating' => $car->rating,

                // Pricing
                'price_per_hour' => $car->price_per_hour,
                'price_per_hour_egp' => $car->price_per_hour_in_egp,
                'has_tiered_pricing' => $car->pricingTiers->isNotEmpty(),
                'pricing_tiers' => $car->pricingTiers->map(fn($tier) => [
                    'description' => $tier->description,
                    'price_per_hour' => $tier->price_per_hour,
                    'price_per_hour_egp' => $tier->price_per_hour_in_egp,
                ]),

                // Features & Specs
                'features' => $car->features ?? [],
                'description' => $car->description,

                // Availability
                'availability' => $car->availability->value,
                'is_available' => $car->availability->value === 'available',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'cars' => $comparison,
                'comparison_count' => $comparison->count(),
            ],
        ]);
    }
}
