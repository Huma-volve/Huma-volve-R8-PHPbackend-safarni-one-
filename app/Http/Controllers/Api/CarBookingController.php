<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarBooking;
use App\Models\BookingExtra;
use App\Models\CarExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarBookingController extends Controller
{
    /**
     * Create a new car booking
     * POST /api/bookings/cars
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'pickup_datetime' => 'required|date|after:now',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'driver_age' => 'required|integer|min:21|max:100',
            'driver_license' => 'required|string|max:255',
            'extras' => 'nullable|array',
            'extras.*.id' => 'required|exists:car_extras,id',
            'extras.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Check availability
            $car = Car::with('pricingTiers')->findOrFail($validated['car_id']);

            if (!$car->isAvailableForPeriod($validated['pickup_datetime'], $validated['dropoff_datetime'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car is not available for the selected period',
                ], 422);
            }

            // 2. Calculate total hours
            $pickup = Carbon::parse($validated['pickup_datetime']);
            $dropoff = Carbon::parse($validated['dropoff_datetime']);
            $totalHours = $pickup->diffInHours($dropoff);
            if ($pickup->diffInMinutes($dropoff) % 60 > 0) {
                $totalHours++; // Round up partial hours
            }

            // 3. Calculate base price
            $basePrice = $car->calculatePrice($totalHours);

            // 4. Calculate extras price
            $extrasPrice = 0;
            $days = ceil($totalHours / 24);

            if (isset($validated['extras'])) {
                foreach ($validated['extras'] as $extraData) {
                    $extra = CarExtra::find($extraData['id']);
                    $quantity = $extraData['quantity'];
                    $extrasPrice += $extra->calculatePrice($days) * $quantity;
                }
            }

            // 5. Calculate total price
            $totalPrice = $basePrice + $extrasPrice;

            // 6. Create main booking
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'category' => 'car',
                'item_id' => $car->id,
                'total_price' => $totalPrice,
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            // 7. Create car booking details
            $carBooking = CarBooking::create([
                'booking_id' => $booking->id,
                'car_id' => $car->id,
                'pickup_datetime' => $validated['pickup_datetime'],
                'dropoff_datetime' => $validated['dropoff_datetime'],
                'total_hours' => $totalHours,
                'pickup_location' => $validated['pickup_location'],
                'dropoff_location' => $validated['dropoff_location'],
                'driver_age' => $validated['driver_age'],
                'driver_license' => $validated['driver_license'],
                'base_price' => $basePrice,
                'extras_price' => $extrasPrice,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // 8. Attach extras
            if (isset($validated['extras'])) {
                foreach ($validated['extras'] as $extraData) {
                    $extra = CarExtra::find($extraData['id']);
                    $quantity = $extraData['quantity'];
                    $price = $extra->calculatePrice($days) * $quantity;

                    BookingExtra::create([
                        'car_booking_id' => $carBooking->id,
                        'car_extra_id' => $extra->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }
            }

            // 9. Load relationships for response
            $booking->load(['carBooking.car', 'carBooking.extras.carExtra']);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'next_step' => 'payment',
                ],
            ], 201);
        });
    }

    /**
     * Get user's car bookings
     * GET /api/bookings/cars
     */
    public function index(Request $request)
    {
        $query = Booking::with(['carBooking.car.images', 'carBooking.extras.carExtra', 'payment'])
            ->where('user_id', auth()->id())
            ->forCar();

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $bookings = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Get specific booking details
     * GET /api/bookings/cars/{id}
     */
    public function show($id)
    {
        $booking = Booking::with([
            'carBooking.car.images',
            'carBooking.extras.carExtra',
            'payment',
            'user:id,name,email,phone',
        ])
            ->where('user_id', auth()->id())
            ->forCar()
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Cancel a booking
     * POST /api/bookings/cars/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('carBooking')
            ->where('user_id', auth()->id())
            ->forCar()
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->carBooking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
        ]);
    }

    /**
     * Confirm a booking (Admin only)
     * POST /api/bookings/cars/{id}/confirm
     */
    public function confirm($id)
    {
        $booking = Booking::with('carBooking')->forCar()->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be confirmed',
            ], 422);
        }

        $booking->update(['status' => 'confirmed']);
        $booking->carBooking->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully',
        ]);
    }
}
