<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface AirportRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find airport by IATA code.
     */
    public function findByCode(string $code): ?object;

    /**
     * Search airports by city or name.
     */
    public function search(string $query): Collection;

    /**
     * Get airports by city.
     */
    public function getByCity(string $city): Collection;
}