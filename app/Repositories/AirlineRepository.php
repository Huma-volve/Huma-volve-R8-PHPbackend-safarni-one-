<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\AirlineRepositoryInterface;
use App\Models\Airline;
use Illuminate\Database\Eloquent\Collection;

class AirlineRepository extends BaseRepository implements AirlineRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Airline $model)
    {
        parent::__construct($model);
    }

    /**
     * Find airline by IATA code.
     */
    public function findByCode(string $code): ?object
    {
        return $this->model->where('code', strtoupper($code))->first();
    }

    /**
     * Get active airlines only.
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Search airlines by name.
     */
    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->get();
    }
}