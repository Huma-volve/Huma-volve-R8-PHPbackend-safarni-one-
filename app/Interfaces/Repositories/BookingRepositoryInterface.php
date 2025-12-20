<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get bookings by user ID.
     */
    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get bookings by category.
     */
    public function getByCategory(string $category): Collection;

    /**
     * Get booking with all relations.
     */
    public function findWithRelations(int $id): ?object;

    /**
     * Update booking status.
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): bool;

    /**
     * Generate unique PNR.
     */
    public function generatePnr(): string;
}