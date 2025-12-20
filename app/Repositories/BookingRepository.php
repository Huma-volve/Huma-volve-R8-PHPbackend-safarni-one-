<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Get bookings by user ID.
     */
    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['categoryData', 'detail', 'passengers', 'payments'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get bookings by category.
     */
    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->with(['user', 'detail', 'passengers'])
            ->where('category', $category)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get booking with all relations.
     */
    public function findWithRelations(int $id): ?object
    {
        return $this->model
            ->with(['user', 'categoryData', 'detail', 'passengers', 'payments'])
            ->find($id);
    }

    /**
     * Update booking status.
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): bool
    {
        return $this->update($id, ['payment_status' => $paymentStatus]);
    }

    /**
     * Generate unique PNR.
     */
    public function generatePnr(): string
    {
        do {
            $pnr = strtoupper(Str::random(6));
        } while ($this->model->where('pnr', $pnr)->exists());

        return $pnr;
    }
}