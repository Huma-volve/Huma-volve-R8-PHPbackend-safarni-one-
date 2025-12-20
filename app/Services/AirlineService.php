<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\AirlineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AirlineService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AirlineRepositoryInterface $airlineRepository
    ) {}

    /**
     * Get all airlines.
     */
    public function getAllAirlines(): Collection
    {
        return $this->airlineRepository->all();
    }

    /**
     * Get paginated airlines.
     */
    public function getPaginatedAirlines(int $perPage = 15): LengthAwarePaginator
    {
        return $this->airlineRepository->paginate($perPage);
    }

    /**
     * Get active airlines only.
     */
    public function getActiveAirlines(): Collection
    {
        return $this->airlineRepository->getActive();
    }

    /**
     * Find airline by ID.
     */
    public function getAirlineById(int $id): Model
    {
        return $this->airlineRepository->findOrFail($id);
    }

    /**
     * Find airline by IATA code.
     */
    public function getAirlineByCode(string $code): ?object
    {
        return $this->airlineRepository->findByCode($code);
    }

    /**
     * Search airlines.
     */
    public function searchAirlines(string $query): Collection
    {
        return $this->airlineRepository->search($query);
    }

    /**
     * Create new airline.
     */
    public function createAirline(array $data): Model
    {
        $data['code'] = strtoupper($data['code']);
        return $this->airlineRepository->create($data);
    }

    /**
     * Update airline.
     */
    public function updateAirline(int $id, array $data): bool
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        return $this->airlineRepository->update($id, $data);
    }

    /**
     * Delete airline.
     */
    public function deleteAirline(int $id): bool
    {
        return $this->airlineRepository->delete($id);
    }
}