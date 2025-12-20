<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\FlightRepositoryInterface;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FlightRepository extends BaseRepository implements FlightRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Flight $model)
    {
        parent::__construct($model);
    }

    /**
     * Search flights by origin, destination, and date.
     */
    public function search(
        string $origin,
        string $destination,
        string $date,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['airline', 'originAirport', 'destinationAirport', 'aircraft'])
            ->whereHas('originAirport', fn($q) => $q->where('code', strtoupper($origin)))
            ->whereHas('destinationAirport', fn($q) => $q->where('code', strtoupper($destination)))
            ->whereDate('departure_time', $date)
            ->where('is_active', true);

        // Apply filters
        if (isset($filters['stops'])) {
            $query->where('stops', $filters['stops']);
        }

        if (isset($filters['price_min'])) {
            $query->where('base_price_egp', '>=', $filters['price_min'] * 100);
        }

        if (isset($filters['price_max'])) {
            $query->where('base_price_egp', '<=', $filters['price_max'] * 100);
        }

        if (isset($filters['airline_id'])) {
            $query->where('airline_id', $filters['airline_id']);
        }

        if (isset($filters['departure_time_range'])) {
            $this->applyTimeRangeFilter($query, $filters['departure_time_range']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'departure_time';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Apply time range filter.
     */
    protected function applyTimeRangeFilter($query, string $range): void
    {
        match ($range) {
            'morning' => $query->whereTime('departure_time', '>=', '06:00:00')
                               ->whereTime('departure_time', '<', '12:00:00'),
            'afternoon' => $query->whereTime('departure_time', '>=', '12:00:00')
                                 ->whereTime('departure_time', '<', '18:00:00'),
            'evening' => $query->whereTime('departure_time', '>=', '18:00:00')
                               ->whereTime('departure_time', '<', '24:00:00'),
            'night' => $query->whereTime('departure_time', '>=', '00:00:00')
                             ->whereTime('departure_time', '<', '06:00:00'),
            default => null,
        };
    }

    /**
     * Get flights by airline.
     */
    public function getByAirline(int $airlineId): Collection
    {
        return $this->model
            ->with(['originAirport', 'destinationAirport'])
            ->where('airline_id', $airlineId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get active flights only.
     */
    public function getActive(): Collection
    {
        return $this->model
            ->with(['airline', 'originAirport', 'destinationAirport'])
            ->where('is_active', true)
            ->get();
    }

    /**
     * Find flight with all relationships.
     */
    public function findWithRelations(string $id): ?object
    {
        return $this->model
            ->with(['airline', 'originAirport', 'destinationAirport', 'aircraft', 'seats'])
            ->find($id);
    }

    /**
     * Get flights for comparison.
     */
    public function getForComparison(array $flightIds): Collection
    {
        return $this->model
            ->with(['airline', 'originAirport', 'destinationAirport'])
            ->whereIn('id', $flightIds)
            ->get();
    }
}