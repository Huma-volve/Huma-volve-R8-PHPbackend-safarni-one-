<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find record by ID.
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or fail.
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * Create new record.
     */
    public function create(array $data): Model;

    /**
     * Update existing record.
     */
    public function update(int|string $id, array $data): bool;

    /**
     * Delete record.
     */
    public function delete(int|string $id): bool;

    /**
     * Find records by criteria.
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection;

    /**
     * Find first record by criteria.
     */
    public function findFirstWhere(array $criteria, array $columns = ['*']): ?Model;
}