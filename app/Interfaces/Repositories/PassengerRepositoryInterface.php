<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface PassengerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get passengers by booking ID.
     */
    public function getByBooking(int $bookingId): Collection;

    /**
     * Create multiple passengers for a booking.
     */
    public function createMany(int $bookingId, array $passengers): Collection;
}