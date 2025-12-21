<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'profile_image',
        'status',
        'is_verified',
        'is_admin',
        'role',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_admin' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || $this->is_admin === true;
    }

    /**
     * Check if user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    /**
     * Check if user is a guest.
     */
    public function isGuest(): bool
    {
        return $this->role === UserRole::GUEST;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->role === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if email is verified.
     */
    public function isEmailVerified(): bool
    {
        return $this->is_verified === true;
    }

    /**
     * Mark email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Mark email as unverified.
     */
    public function markEmailAsUnverified(): bool
    {
        return $this->update([
            'is_verified' => false,
            'email_verified_at' => null,
        ]);
    }

    /**
     * Get user's bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get user's reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get user's favorites.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
