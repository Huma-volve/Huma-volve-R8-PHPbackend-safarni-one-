<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\AirportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AirportService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AirportRepositoryInterface $airportRepository
    ) {}

    /**
     * Get all airports.
     */
    public function getAllAirports(): Collection
    {
        return $this->airportRepository->all();
    }

    /**
     * Get paginated airports.
     */
    public function getPaginatedAirports(int $perPage = 15): LengthAwarePaginator
    {
        return $this->airportRepository->paginate($perPage);
    }

    /**
     * Find airport by ID.
     */
    public function getAirportById(int $id): Model
    {
        return $this->airportRepository->findOrFail($id);
    }

    /**
     * Find airport by IATA code.
     */
    public function getAirportByCode(string $code): ?object
    {
        return $this->airportRepository->findByCode($code);
    }

    /**
     * Search airports.
     */
    public function searchAirports(string $query): Collection
    {
        return $this->airportRepository->search($query);
    }

    /**
     * Create new airport.
     */
    public function createAirport(array $data): Model
    {
        $data['code'] = strtoupper($data['code']);
        return $this->airportRepository->create($data);
    }

    /**
     * Update airport.
     */
    public function updateAirport(int $id, array $data): bool
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        return $this->airportRepository->update($id, $data);
    }

    /**
     * Delete airport.
     */
    public function deleteAirport(int $id): bool
    {
        return $this->airportRepository->delete($id);
    }
}