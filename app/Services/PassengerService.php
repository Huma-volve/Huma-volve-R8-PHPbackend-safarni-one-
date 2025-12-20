<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\PassengerRepositoryInterface;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PassengerService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected PassengerRepositoryInterface $passengerRepository,
        protected BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Get passengers for a booking.
     */
    public function getBookingPassengers(int $bookingId): Collection
    {
        return $this->passengerRepository->getByBooking($bookingId);
    }

    /**
     * Get passenger by ID.
     */
    public function getPassengerById(int $id): ?Model
    {
        return $this->passengerRepository->find($id);
    }

    /**
     * Add passengers to a booking.
     */
    public function addPassengers(int $bookingId, array $passengers): Collection
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            return collect();
        }

        return $this->passengerRepository->createMany($bookingId, $passengers);
    }

    /**
     * Update passenger.
     */
    public function updatePassenger(int $id, array $data): bool
    {
        return $this->passengerRepository->update($id, $data);
    }

    /**
     * Delete passenger.
     */
    public function deletePassenger(int $id): bool
    {
        return $this->passengerRepository->delete($id);
    }

    /**
     * Validate passport expiry against flight date.
     */
    public function validatePassportExpiry(string $passportExpiry, string $flightDate): bool
    {
        $passportExpiryDate = \Carbon\Carbon::parse($passportExpiry);
        $flightDateTime = \Carbon\Carbon::parse($flightDate);

        // Passport should be valid for at least 6 months after flight
        return $passportExpiryDate->isAfter($flightDateTime->addMonths(6));
    }
}