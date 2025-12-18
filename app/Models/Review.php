<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',     // FK
        'item_id',      // Polymorphic ID
        'title',
        'comment',      // Review body
        'rating',       // 1-5 scale
        'photos_json',
        'status',       // pending, approved, rejected
    ];

    protected function casts(): array
    {
        return [
            'photos_json' => 'array',
        ];
    }

    // Belongs to a User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Belongs to a Category
    public function categoryData(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'key');
    }
}