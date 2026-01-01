<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class RoomController extends Controller
{
    use ApiResponse;

    /**
     * List rooms for a specific hotel.
     */
    public function index(Request $request, int $hotelId): JsonResponse
    {
        $query = Room::where('hotel_id', $hotelId);

        // Filter by availability if dates are provided
        if ($request->has(['check_in', 'check_out'])) {
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', [
                    \App\Enums\BookingStatus::CONFIRMED->value,
                    \App\Enums\BookingStatus::PENDING->value,
                    \App\Enums\BookingStatus::COMPLETED->value,
                ])
                    ->whereHas('detail', function ($q2) use ($checkIn, $checkOut) {
                        // Check date overlap: (BookStart < ReqEnd) AND (BookEnd > ReqStart)
                        // Using JSON extraction for meta field
                        $q2->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.check_in')) AS DATE) < ?", [$checkOut])
                            ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.check_out')) AS DATE) > ?", [$checkIn]);
                    });
            });
        }

        $rooms = $query->get();

        // Format the image URL
        $rooms->transform(function ($room) {
            // Assuming main_image is just a filename, prepend storage URL if needed
            // Or if it's a full URL, leave it. 
            // Based on previous code, we might need to prepend 'storage/'
            $room->main_image = $room->main_image ? url('storage/' . $room->main_image) : null;
            return $room;
        });

        // We can create a Resource class properly later, for now returning collection directly
        return $this->successResponse($rooms);
    }

    public function show(int $id): JsonResponse
    {
        $room = Room::with(['hotel.images', 'hotel.reviews.user'])->find($id);

        if (!$room) {
            return $this->errorResponse('Room not found', 404);
        }

        // Format room image
        $room->main_image = $room->main_image ? url('storage/' . $room->main_image) : null;

        // Process Hotel Reviews stats
        $hotel = $room->hotel;
        $totalReviews = $hotel->reviews->count();
        // Assuming hotel->rating is pre-calculated or stored.
        // If we want to calculate average from reviews:
        // $avgRating = $hotel->reviews->avg('rating'); 
        // using stored rating for now as per model.

        // Format Gallery
        $gallery = $hotel->images->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => url('storage/' . $img->image_path),
                'user_id' => $img->user_id,
            ];
        });

        // Format Reviews
        $reviews = $hotel->reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'user_name' => $review->user->name ?? 'Guest',
                'user_avatar' => $review->user->avatar ? url('storage/' . $review->user->avatar) : null, // Assuming avatar field exists
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->diffForHumans(),
                'photos' => $review->photos_json, // Assuming generic approach
            ];
        });

        $responseData = [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'price_per_night' => $room->price_per_night,
                'main_image' => $room->main_image,
                'bed_type' => $room->bed_type,
                'occupancy' => $room->occupancy,
                'area' => $room->area, // added field
                'bathrooms' => $room->bathrooms, // added field
            ],
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'description' => $hotel->description,
                'rating' => $hotel->rating,
                'discount' => $hotel->discount,
                'total_reviews' => $totalReviews,
            ],
            'gallery' => [
                'count' => $gallery->count(),
                'images' => $gallery,
            ],
            'reviews' => $reviews,
        ];

        return $this->successResponse($responseData);
    }
}
