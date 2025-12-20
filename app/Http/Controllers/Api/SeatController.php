<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Seat\LockSeatRequest;
use App\Http\Resources\SeatResource;
use App\Services\SeatService;
use Illuminate\Http\JsonResponse;

class SeatController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SeatService $seatService
    ) {}

    /**
     * Get seats for a flight grouped by class.
     */
    public function index(string $flightId): JsonResponse
    {
        $seats = $this->seatService->getFlightSeats($flightId);

        if (empty($seats)) {
            return $this->notFoundResponse('Flight not found or has no seats');
        }

        return $this->successResponse([
            'flight_id' => $flightId,
            'seats_by_class' => $seats,
        ]);
    }

    /**
     * Get a specific seat.
     */
    public function show(string $id): JsonResponse
    {
        $seat = $this->seatService->getSeatById($id);

        if (!$seat) {
            return $this->notFoundResponse('Seat not found');
        }

        return $this->successResponse(
            new SeatResource($seat)
        );
    }

    /**
     * Lock a seat temporarily.
     */
    public function lock(LockSeatRequest $request): JsonResponse
    {
        $result = $this->seatService->lockSeat($request->input('seat_id'));

        if (!$result['success']) {
            return $this->errorResponse($result['message']);
        }

        return $this->successResponse([
            'message' => $result['message'],
            'expires_at' => $result['expires_at'],
        ]);
    }

    /**
     * Release a locked seat.
     */
    public function release(string $seatId): JsonResponse
    {
        $released = $this->seatService->releaseSeat($seatId);

        if (!$released) {
            return $this->errorResponse('Failed to release seat or seat was not locked');
        }

        return $this->successResponse(
            null,
            'Seat released successfully'
        );
    }
}