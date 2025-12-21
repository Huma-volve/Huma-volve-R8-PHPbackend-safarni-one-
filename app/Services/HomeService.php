<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\TourResource;
use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Interfaces\Repositories\TourRepositoryInterface;

/**
 * Service for aggregating home page data.
 */
class HomeService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly TourRepositoryInterface $tourRepository,
    ) {}

    /**
     * Get aggregated payload for home page.
     *
     * @return array<string, mixed>
     */
    public function getHomePayload(): array
    {
        return [
            'categories' => CategoryResource::collection(
                $this->categoryRepository->getAllActive()
            ),
            'recommendations' => TourResource::collection(
                $this->tourRepository->getFeaturedTours(5)
            ),
            'tours' => TourResource::collection(
                $this->tourRepository->getLatestTours(10)
            ),
        ];
    }
}