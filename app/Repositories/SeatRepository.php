<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\SeatRepositoryInterface;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SeatRepository extends BaseRepository implements SeatRepositoryInterface
{
    /**
     * Cache key prefix for seat locks.
     */
    protected const LOCK_PREFIX = 'seat_lock_';

    /**
     * Create a new repository instance.
     */
    public function __construct(Seat $model)
    {
        parent::__construct($model);
    }

    /**
     * Get seats by flight ID.
     */
    public function getByFlight(string $flightId): Collection
    {
        return $this->model
            ->where('flight_id', $flightId)
            ->orderBy('class')
            ->orderBy('row')
            ->orderBy('column')
            ->get();
    }

    /**
     * Get available seats by flight ID.
     */
    public function getAvailableByFlight(string $flightId): Collection
    {
        return $this->model
            ->where('flight_id', $flightId)
            ->where('is_available', true)
            ->orderBy('class')
            ->orderBy('row')
            ->orderBy('column')
            ->get()
            ->filter(fn($seat) => !$this->isLocked($seat->id));
    }

    /**
     * Get seats by flight ID grouped by class.
     */
    public function getByFlightGroupedByClass(string $flightId): array
    {
        $seats = $this->getByFlight($flightId);

        $grouped = [];
        foreach ($seats as $seat) {
            $class = $seat->class->value ?? $seat->class;
            if (!isset($grouped[$class])) {
                $grouped[$class] = [];
            }

            $grouped[$class][] = [
                'id' => $seat->id,
                'row' => $seat->row,
                'column' => $seat->column,
                'designation' => $seat->row . $seat->column,
                'is_available' => $seat->is_available && !$this->isLocked($seat->id),
                'is_locked' => $this->isLocked($seat->id),
                'price_modifier_egp' => $seat->price_modifier_egp,
            ];
        }

        return $grouped;
    }

    /**
     * Lock a seat temporarily.
     */
    public function lockSeat(string $seatId, int $minutes = 10): bool
    {
        $seat = $this->find($seatId);

        if (!$seat || !$seat->is_available || $this->isLocked($seatId)) {
            return false;
        }

        Cache::put(
            self::LOCK_PREFIX . $seatId,
            true,
            now()->addMinutes($minutes)
        );

        return true;
    }

    /**
     * Release a locked seat.
     */
    public function releaseSeat(string $seatId): bool
    {
        return Cache::forget(self::LOCK_PREFIX . $seatId);
    }

    /**
     * Check if seat is locked.
     */
    protected function isLocked(string $seatId): bool
    {
        return Cache::has(self::LOCK_PREFIX . $seatId);
    }

    /**
     * Check if seat is available.
     */
    public function isAvailable(string $seatId): bool
    {
        $seat = $this->find($seatId);

        if (!$seat) {
            return false;
        }

        return $seat->is_available && !$this->isLocked($seatId);
    }

    /**
     * Book a seat (mark as unavailable).
     */
    public function bookSeat(string $seatId): bool
    {
        $seat = $this->find($seatId);

        if (!$seat || !$seat->is_available) {
            return false;
        }

        $this->releaseSeat($seatId);

        return $seat->update(['is_available' => false]);
    }
}