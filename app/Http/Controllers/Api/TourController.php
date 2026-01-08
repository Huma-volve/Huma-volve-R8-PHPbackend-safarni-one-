<?php

namespace App\Http\Controllers\Api;

use App\Models\Tour;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TourController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected TourService $tourService
    ) {}
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $filters = [];
        
        // Handle price range filters (convert from EGP to piasters if needed)
        // Prices in database are stored in piasters (cents), so if user sends EGP, multiply by 100
        if ($request->filled('min_price')) {
            $minPrice = (int) $request->input('min_price');
            // If price seems to be in EGP (less than 1000), convert to piasters
            $filters['min_price'] = $minPrice < 1000 ? $minPrice * 100 : $minPrice;
        }
        
        if ($request->filled('max_price')) {
            $maxPrice = (int) $request->input('max_price');
            // If price seems to be in EGP (less than 1000), convert to piasters
            $filters['max_price'] = $maxPrice < 1000 ? $maxPrice * 100 : $maxPrice;
        }

        // Get sort parameter
        $sortBy = $request->input('sort_by');

        $tours = $this->tourService->getToursWithFilters($filters, $sortBy);
        return TourResource::collection($tours);
    }

    public function show(Tour $tour): JsonResponse
    {
        $tour = $this->tourService->getTourById($tour->id);
        if (!$tour) {
            return $this->notFoundResponse('Tour not found');
        }
        return $this->successResponse(new TourResource($tour));
    }

    public function availability(): JsonResponse
    {
        $tours = $this->tourService->getAllTours(5);
        return $this->successResponse([
            'data' => TourResource::collection($tours),
        ]);
    }

    public function recommendations(): JsonResponse
    {
        $tours = $this->tourService->getAllTours(5);
        return $this->successResponse([
            'data' => TourResource::collection($tours),
        ]);
    }
    public function destination(Request $request): JsonResponse
    {
        $query = Tour::select('location')->distinct();

        if ($request->filled('search')) {
            $query->where('location', 'like', '%' . $request->search . '%');
        }

        $destinations = $query->limit(10)->pluck('location');

        return $this->successResponse([
            $destinations,
        ]);
    }
    public function toursInDestination(Request $request): JsonResponse
    {
        $tours = Tour::where('location', $request->destination)->get();
        if (!$tours) {
            return $this->notFoundResponse('Tours not found');
        }
        return $this->successResponse([
            'data' => TourResource::collection($tours),
        ]);
    }
}
