<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\PassengerRepositoryInterface;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Collection;

class PassengerRepository extends BaseRepository implements PassengerRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Passenger $model)
    {
        parent::__construct($model);
    }

    /**
     * Get passengers by booking ID.
     */
    public function getByBooking(int $bookingId): Collection
    {
        return $this->model
            ->where('booking_id', $bookingId)
            ->get();
    }

    /**
     * Create multiple passengers for a booking.
     */
    public function createMany(int $bookingId, array $passengers): Collection
    {
        $created = collect();

        foreach ($passengers as $passengerData) {
            $passengerData['booking_id'] = $bookingId;
            $passenger = $this->create($passengerData);
            $created->push($passenger);
        }

        return $created;
    }
}