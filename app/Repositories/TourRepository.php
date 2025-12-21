<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\TourRepositoryInterface;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository for Tour data access operations.
 */
class TourRepository extends BaseRepository implements TourRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Tour $model)
    {
        parent::__construct($model);
    }

    /**
     * Get featured tours that are active and not expired.
     */
    public function getFeaturedTours(int $limit = 5): Collection
    {
        return $this->model
            ->available()
            ->featured()
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest tours that are active and not expired.
     */
    public function getLatestTours(int $limit = 10): Collection
    {
        return $this->model
            ->available()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find tour by slug.
     */
    public function findBySlug(string $slug): ?Tour
    {
        return $this->model
            ->where('slug', $slug)
            ->first();
    }
}