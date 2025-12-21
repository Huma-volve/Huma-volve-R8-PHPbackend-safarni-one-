<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find user by ID.
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find user by Google ID.
     */
    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    /**
     * Update user data.
     */
    public function update(int $id, array $data): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    /**
     * Soft delete user account.
     */
    public function softDelete(int $id): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Deactivate user account.
     */
    public function deactivate(int $id): bool
    {
        return $this->update($id, ['status' => 'inactive']);
    }
}
