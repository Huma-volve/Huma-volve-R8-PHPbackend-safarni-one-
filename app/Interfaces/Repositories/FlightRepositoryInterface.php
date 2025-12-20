<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FlightRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Search flights by origin, destination, and date.
     */
    public function search(
        string $origin,
        string $destination,
        string $date,
        array $filters = []
    ): LengthAwarePaginator;

    /**
     * Get flights by airline.
     */
    public function getByAirline(int $airlineId): Collection;

    /**
     * Get active flights only.
     */
    public function getActive(): Collection;

    /**
     * Find flight with all relationships.
     */
    public function findWithRelations(string $id): ?object;

    /**
     * Get flights for comparison.
     */
    public function getForComparison(array $flightIds): Collection;
}