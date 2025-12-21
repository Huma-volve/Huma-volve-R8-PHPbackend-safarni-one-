<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for Category data access operations.
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active categories.
     */
    public function getAllActive(): Collection;

    /**
     * Find category by its key/slug.
     */
    public function findByKey(string $key): ?Category;
}