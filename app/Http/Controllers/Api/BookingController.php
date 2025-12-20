<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Booking\CheckoutBookingRequest;
use App\Http\Requests\Booking\CreateBookingSummaryRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Get authenticated user's bookings.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $this->bookingService->getUserBookings(
            auth()->id(),
            $request->input('per_page', 15)
        );

        return BookingResource::collection($bookings);
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            return $this->notFoundResponse('Booking not found');
        }

        // Check ownership
        if ($booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this booking');
        }

        return $this->successResponse(
            new BookingResource($booking)
        );
    }

    /**
     * Create booking summary (pre-checkout).
     */
    public function summary(CreateBookingSummaryRequest $request): JsonResponse
    {
        $result = $this->bookingService->createSummary($request->validated());

        if (!$result['success']) {
            return $this->errorResponse($result['message']);
        }

        return $this->successResponse(
            $result['summary'],
            'Booking summary created'
        );
    }

    /**
     * Process checkout.
     */
    public function checkout(CheckoutBookingRequest $request): JsonResponse
    {
        $result = $this->bookingService->checkout(
            $request->input('booking_token'),
            auth()->id()
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message']);
        }

        return $this->createdResponse(
            new BookingResource($result['booking']),
            'Booking created successfully'
        );
    }

    /**
     * Cancel a booking.
     */
    public function cancel(int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            return $this->notFoundResponse('Booking not found');
        }

        // Check ownership
        if ($booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this booking');
        }

        $cancelled = $this->bookingService->cancelBooking($id);

        if (!$cancelled) {
            return $this->errorResponse('Failed to cancel booking');
        }

        return $this->successResponse(
            null,
            'Booking cancelled successfully'
        );
    }
}