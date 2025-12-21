<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository for Category data access operations.
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active categories.
     */
    public function getAllActive(): Collection
    {
        return $this->model
            ->orderBy('id')
            ->get();
    }

    /**
     * Find category by its key/slug.
     */
    public function findByKey(string $key): ?Category
    {
        return $this->model
            ->where('key', $key)
            ->first();
    }
}