<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',             
        'title',           
        'description',     
        'image',           
        'editable_fields', 
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'editable_fields' => 'array',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'category', 'key');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'category', 'key');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'category', 'key');
    }
}