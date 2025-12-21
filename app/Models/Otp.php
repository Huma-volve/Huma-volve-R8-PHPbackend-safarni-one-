<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OtpType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'code',
        'type',
        'is_used',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OtpType::class,
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Scope for valid (non-expired, non-used) OTPs.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for a specific email.
     */
    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    /**
     * Scope for a specific type.
     */
    public function scopeOfType(Builder $query, OtpType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /**
     * Check if the OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is still valid.
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Mark the OTP as used.
     */
    public function markAsUsed(): bool
    {
        return $this->update(['is_used' => true]);
    }
}
