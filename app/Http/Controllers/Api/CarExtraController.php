<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarExtra;
use Illuminate\Http\Request;

class CarExtraController extends Controller
{
    /**
     * Get all available extras
     * GET /api/car-extras
     */
    public function index(Request $request)
    {
        $query = CarExtra::query()->available();

        // Filter by pricing type
        if ($request->has('pricing_type')) {
            $query->byPricingType($request->pricing_type);
        }

        $extras = $query->get()->map(function ($extra) {
            return [
                'id' => $extra->id,
                'name' => $extra->name,
                'pricing_type' => $extra->pricing_type,
                'price' => $extra->price,
                'price_egp' => $extra->price_in_egp,
                'is_available' => $extra->is_available,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $extras,
        ]);
    }

    /**
     * Calculate extra price for specific duration
     * POST /api/car-extras/{id}/calculate-price
     */
    public function calculatePrice(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $extra = CarExtra::findOrFail($id);
        $totalPrice = $extra->calculatePrice($request->days);

        return response()->json([
            'success' => true,
            'data' => [
                'extra_id' => $extra->id,
                'extra_name' => $extra->name,
                'pricing_type' => $extra->pricing_type,
                'days' => $request->days,
                'unit_price' => $extra->price,
                'unit_price_egp' => $extra->price_in_egp,
                'total_price' => $totalPrice,
                'total_price_egp' => $totalPrice / 100,
            ],
        ]);
    }
}
