<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\SeatRepositoryInterface;
use App\Interfaces\Repositories\FlightRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SeatService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected SeatRepositoryInterface $seatRepository,
        protected FlightRepositoryInterface $flightRepository
    ) {}

    /**
     * Get seats for a flight grouped by class.
     */
    public function getFlightSeats(string $flightId): array
    {
        $flight = $this->flightRepository->find($flightId);

        if (!$flight) {
            return [];
        }

        return $this->seatRepository->getByFlightGroupedByClass($flightId);
    }

    /**
     * Get available seats for a flight.
     */
    public function getAvailableSeats(string $flightId): Collection
    {
        return $this->seatRepository->getAvailableByFlight($flightId);
    }

    /**
     * Lock a seat for booking.
     */
    public function lockSeat(string $seatId, int $minutes = 10): array
    {
        $seat = $this->seatRepository->find($seatId);

        if (!$seat) {
            return [
                'success' => false,
                'message' => 'Seat not found',
            ];
        }

        if (!$this->seatRepository->isAvailable($seatId)) {
            return [
                'success' => false,
                'message' => 'Seat is not available or already locked',
            ];
        }

        $locked = $this->seatRepository->lockSeat($seatId, $minutes);

        return [
            'success' => $locked,
            'message' => $locked ? 'Seat locked successfully' : 'Failed to lock seat',
            'expires_at' => $locked ? now()->addMinutes($minutes)->toISOString() : null,
        ];
    }

    /**
     * Release a locked seat.
     */
    public function releaseSeat(string $seatId): bool
    {
        return $this->seatRepository->releaseSeat($seatId);
    }

    /**
     * Book a seat.
     */
    public function bookSeat(string $seatId): bool
    {
        return $this->seatRepository->bookSeat($seatId);
    }

    /**
     * Get seat by ID.
     */
    public function getSeatById(string $id): ?Model
    {
        return $this->seatRepository->find($id);
    }

    /**
     * Create seats for a flight.
     */
    public function createSeatsForFlight(string $flightId, array $seatConfig): Collection
    {
        $seats = collect();

        foreach ($seatConfig as $config) {
            for ($row = $config['start_row']; $row <= $config['end_row']; $row++) {
                foreach ($config['columns'] as $column) {
                    $seat = $this->seatRepository->create([
                        'flight_id' => $flightId,
                        'class' => $config['class'],
                        'row' => $row,
                        'column' => $column,
                        'is_available' => true,
                        'price_modifier_egp' => $config['price_modifier_egp'] ?? 0,
                    ]);
                    $seats->push($seat);
                }
            }
        }

        return $seats;
    }
}