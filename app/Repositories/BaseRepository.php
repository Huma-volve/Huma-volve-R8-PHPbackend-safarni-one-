<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected Model $model
    ) {}

    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find record by ID.
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->find($id);
    }

    /**
     * Find record by ID or fail.
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->model->select($columns)->findOrFail($id);
    }

    /**
     * Create new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update existing record.
     */
    public function update(int|string $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    /**
     * Delete record.
     */
    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    /**
     * Find records by criteria.
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where($criteria)->get();
    }

    /**
     * Find first record by criteria.
     */
    public function findFirstWhere(array $criteria, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->where($criteria)->first();
    }
}