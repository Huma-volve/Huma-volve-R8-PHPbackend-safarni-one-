<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\AirportRepositoryInterface;
use App\Models\Airport;
use Illuminate\Database\Eloquent\Collection;

class AirportRepository extends BaseRepository implements AirportRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Airport $model)
    {
        parent::__construct($model);
    }

    /**
     * Find airport by IATA code.
     */
    public function findByCode(string $code): ?object
    {
        return $this->model->where('code', strtoupper($code))->first();
    }

    /**
     * Search airports by city or name.
     */
    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('city', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->get();
    }

    /**
     * Get airports by city.
     */
    public function getByCity(string $city): Collection
    {
        return $this->model->where('city', $city)->get();
    }
}