<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Find user by ID.
     */
    public function find(int $id): ?User;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by Google ID.
     */
    public function findByGoogleId(string $googleId): ?User;

    /**
     * Update user data.
     */
    public function update(int $id, array $data): bool;

    /**
     * Soft delete user account.
     */
    public function softDelete(int $id): bool;

    /**
     * Deactivate user account.
     */
    public function deactivate(int $id): bool;
}
