<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\FlightRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FlightService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected FlightRepositoryInterface $flightRepository
    ) {}

    /**
     * Search flights.
     */
    public function searchFlights(
        string $origin,
        string $destination,
        string $date,
        array $filters = []
    ): LengthAwarePaginator {
        return $this->flightRepository->search($origin, $destination, $date, $filters);
    }

    /**
     * Get flight by ID with relations.
     */
    public function getFlightById(string $id): ?object
    {
        return $this->flightRepository->findWithRelations($id);
    }

    /**
     * Get flights for comparison.
     */
    public function compareFlights(array $flightIds): Collection
    {
        return $this->flightRepository->getForComparison($flightIds);
    }

    /**
     * Get flights by airline.
     */
    public function getFlightsByAirline(int $airlineId): Collection
    {
        return $this->flightRepository->getByAirline($airlineId);
    }

    /**
     * Create new flight.
     */
    public function createFlight(array $data): Model
    {
        // Convert price to piasters if provided in EGP
        if (isset($data['base_price_egp']) && $data['base_price_egp'] < 10000) {
            $data['base_price_egp'] = (int) ($data['base_price_egp'] * 100);
        }

        return $this->flightRepository->create($data);
    }

    /**
     * Update flight.
     */
    public function updateFlight(string $id, array $data): bool
    {
        // Convert price to piasters if provided in EGP
        if (isset($data['base_price_egp']) && $data['base_price_egp'] < 10000) {
            $data['base_price_egp'] = (int) ($data['base_price_egp'] * 100);
        }

        return $this->flightRepository->update($id, $data);
    }

    /**
     * Delete flight.
     */
    public function deleteFlight(string $id): bool
    {
        return $this->flightRepository->delete($id);
    }

    /**
     * Calculate total price with tax.
     */
    public function calculateTotalPrice(int $basePriceEgp, float $taxPercentage): array
    {
        $taxAmount = (int) ($basePriceEgp * ($taxPercentage / 100));
        $totalPrice = $basePriceEgp + $taxAmount;

        return [
            'base_price' => $basePriceEgp,
            'tax_amount' => $taxAmount,
            'tax_percentage' => $taxPercentage,
            'total_price' => $totalPrice,
            'formatted' => [
                'base_price' => number_format($basePriceEgp / 100, 2) . ' EGP',
                'tax_amount' => number_format($taxAmount / 100, 2) . ' EGP',
                'total_price' => number_format($totalPrice / 100, 2) . ' EGP',
            ],
        ];
    }
}