<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tour model representing bookable tour packages.
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $slug
 * @property string $location
 * @property int $price
 * @property \DateTime $start_date
 * @property \DateTime $end_date
 * @property bool $is_featured
 * @property bool $is_active
 * @property float $rating
 * @property string $thumbnail_url
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property-read Category $category
 */
class Tour extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'location',
        'price',
        'start_date',
        'end_date',
        'is_featured',
        'is_active',
        'rating',
        'thumbnail_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    /**
     * Get the category that owns the tour.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to get only active tours.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only featured tours.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get only tours that haven't started yet.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now());
    }

    /**
     * Scope to get tours available for booking.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->notExpired();
    }
}