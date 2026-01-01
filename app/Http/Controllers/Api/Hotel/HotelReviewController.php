<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Hotel;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HotelReviewController extends BaseApiController
{
    /**
     * List reviews for a hotel.
     */
    public function index(int $hotelId): JsonResponse
    {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return $this->notFoundResponse('Hotel not found');
        }

        $reviews = $hotel->reviews()->with('user')->latest()->paginate(10);

        return $this->successResponse($reviews);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, int $hotelId): JsonResponse
    {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return $this->notFoundResponse('Hotel not found');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'image' => 'nullable|image|max:2048', // Optional review image
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        $review = Review::create([
            'user_id' => auth()->id(), // Assuming auth
            'category' => 'hotels',
            'item_id' => $hotelId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'photos_json' => $imagePath ? [$imagePath] : [],
            'status' => 'approved', // Auto-approve for now
        ]);

        return $this->createdResponse($review, 'Review added successfully');
    }
}
