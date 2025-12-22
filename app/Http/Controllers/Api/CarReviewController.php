<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Car;
use Illuminate\Http\Request;

class CarReviewController extends Controller
{
    /**
     * Get reviews for a specific car
     * GET /api/cars/{carId}/reviews
     */
    public function index($carId)
    {
        $car = Car::findOrFail($carId);

        $reviews = Review::with('user:id,name,profile_image')
            ->forCar($carId)
            ->approved()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'car' => [
                    'id' => $car->id,
                    'brand' => $car->brand,
                    'model' => $car->model,
                    'rating' => $car->rating,
                ],
                'reviews' => $reviews,
            ],
        ]);
    }

    /**
     * Create a review for a car
     * POST /api/cars/{carId}/reviews
     */
    public function store(Request $request, $carId)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'string', // URLs or base64
        ]);

        $car = Car::findOrFail($carId);

        // Check if user already reviewed this car
        $existingReview = Review::where('user_id', auth()->id())
            ->forCar($carId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this car',
            ], 422);
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'category' => 'car',
            'item_id' => $carId,
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
            'photos_json' => $validated['photos'] ?? null,
            'status' => 'pending', // Requires admin approval
        ]);

        // Update car rating
        $car->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully and pending approval',
            'data' => $review,
        ], 201);
    }

    /**
     * Update a review
     * PUT /api/reviews/{id}
     */
    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'string',
        ]);

        $review->update([
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
            'photos_json' => $validated['photos'] ?? null,
            'status' => 'pending', // Reset to pending after edit
        ]);

        // Update car rating
        $car = Car::find($review->item_id);
        $car->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }

    /**
     * Delete a review
     * DELETE /api/reviews/{id}
     */
    public function destroy($id)
    {
        $review = Review::where('user_id', auth()->id())->findOrFail($id);
        $carId = $review->item_id;

        $review->delete();

        // Update car rating
        $car = Car::find($carId);
        $car->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
