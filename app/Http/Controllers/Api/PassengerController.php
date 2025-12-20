<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Passenger\StorePassengerRequest;
use App\Http\Requests\Passenger\UpdatePassengerRequest;
use App\Http\Resources\PassengerResource;
use App\Services\PassengerService;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PassengerController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PassengerService $passengerService,
        protected BookingService $bookingService
    ) {}

    /**
     * Get passengers for a booking.
     */
    public function index(int $bookingId): AnonymousResourceCollection|JsonResponse
    {
        $booking = $this->bookingService->getBookingById($bookingId);

        if (!$booking) {
            return $this->notFoundResponse('Booking not found');
        }

        // Check ownership
        if ($booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this booking');
        }

        $passengers = $this->passengerService->getBookingPassengers($bookingId);

        return PassengerResource::collection($passengers);
    }

    /**
     * Store a newly created passenger.
     */
    public function store(StorePassengerRequest $request): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($request->input('booking_id'));

        if (!$booking) {
            return $this->notFoundResponse('Booking not found');
        }

        // Check ownership
        if ($booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this booking');
        }

        $passengers = $this->passengerService->addPassengers(
            $request->input('booking_id'),
            [$request->except('booking_id')]
        );

        return $this->createdResponse(
            PassengerResource::collection($passengers),
            'Passenger added successfully'
        );
    }

    /**
     * Display the specified passenger.
     */
    public function show(int $id): JsonResponse
    {
        $passenger = $this->passengerService->getPassengerById($id);

        if (!$passenger) {
            return $this->notFoundResponse('Passenger not found');
        }

        // Check ownership through booking
        $booking = $this->bookingService->getBookingById($passenger->booking_id);
        if ($booking && $booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this passenger');
        }

        return $this->successResponse(
            new PassengerResource($passenger)
        );
    }

    /**
     * Update the specified passenger.
     */
    public function update(UpdatePassengerRequest $request, int $id): JsonResponse
    {
        $passenger = $this->passengerService->getPassengerById($id);

        if (!$passenger) {
            return $this->notFoundResponse('Passenger not found');
        }

        // Check ownership through booking
        $booking = $this->bookingService->getBookingById($passenger->booking_id);
        if ($booking && $booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this passenger');
        }

        $this->passengerService->updatePassenger($id, $request->validated());
        $updatedPassenger = $this->passengerService->getPassengerById($id);

        return $this->successResponse(
            new PassengerResource($updatedPassenger),
            'Passenger updated successfully'
        );
    }

    /**
     * Remove the specified passenger.
     */
    public function destroy(int $id): JsonResponse
    {
        $passenger = $this->passengerService->getPassengerById($id);

        if (!$passenger) {
            return $this->notFoundResponse('Passenger not found');
        }

        // Check ownership through booking
        $booking = $this->bookingService->getBookingById($passenger->booking_id);
        if ($booking && $booking->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->forbiddenResponse('You do not have access to this passenger');
        }

        $this->passengerService->deletePassenger($id);

        return $this->successResponse(
            null,
            'Passenger deleted successfully'
        );
    }
}