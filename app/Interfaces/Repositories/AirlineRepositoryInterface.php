<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface AirlineRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find airline by IATA code.
     */
    public function findByCode(string $code): ?object;

    /**
     * Get active airlines only.
     */
    public function getActive(): Collection;

    /**
     * Search airlines by name.
     */
    public function search(string $query): Collection;
}