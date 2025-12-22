<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'item_id',
        'title',
        'comment',
        'rating',
        'photos_json',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'photos_json' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'item_id')
            ->where('category', 'car');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForCar($query, $carId)
    {
        return $query->where('item_id', $carId)->where('category', 'car');
    }

    public function scopeForCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
