<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface SeatRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get seats by flight ID.
     */
    public function getByFlight(string $flightId): Collection;

    /**
     * Get available seats by flight ID.
     */
    public function getAvailableByFlight(string $flightId): Collection;

    /**
     * Get seats by flight ID grouped by class.
     */
    public function getByFlightGroupedByClass(string $flightId): array;

    /**
     * Lock a seat temporarily.
     */
    public function lockSeat(string $seatId, int $minutes = 10): bool;

    /**
     * Release a locked seat.
     */
    public function releaseSeat(string $seatId): bool;

    /**
     * Check if seat is available.
     */
    public function isAvailable(string $seatId): bool;

    /**
     * Book a seat (mark as unavailable).
     */
    public function bookSeat(string $seatId): bool;
}