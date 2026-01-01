<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HotelGalleryController extends BaseApiController
{
    /**
     * List gallery images for a hotel.
     */
    public function index(int $hotelId): JsonResponse
    {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return $this->notFoundResponse('Hotel not found');
        }

        $images = $hotel->images()->with('user')->latest()->get()->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => url('storage/' . $img->image_path),
                'user' => $img->user ? $img->user->name : null,
                'created_at' => $img->created_at,
            ];
        });

        return $this->successResponse($images);
    }

    /**
     * Upload an image to the hotel gallery.
     */
    public function store(Request $request, int $hotelId): JsonResponse
    {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return $this->notFoundResponse('Hotel not found');
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required',
            'images.*' => 'image|max:4096', // Allow multiple images
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('hotels/gallery', 'public');

                $image = HotelImage::create([
                    'hotel_id' => $hotelId,
                    'image_path' => $path,
                    'user_id' => auth()->id(),
                ]);

                $uploadedImages[] = [
                    'id' => $image->id,
                    'url' => url('storage/' . $path),
                ];
            }
        }

        return $this->createdResponse($uploadedImages, 'Images uploaded successfully');
    }
}
