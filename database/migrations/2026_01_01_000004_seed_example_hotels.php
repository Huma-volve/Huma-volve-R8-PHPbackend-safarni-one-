<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Hotel;
use App\Models\Room;

return new class extends Migration {
    public function up(): void
    {
        $hotel1 = Hotel::create([
            'name' => 'Grand Plaza Hotel',
            'description' => 'Luxury stay in the heart of Cairo',
            'address' => '123 Nile corniche',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'rating' => 4.8,
            'main_image' => 'grand_plaza.jpg',
            'discount' => 10
        ]);

        Room::create([
            'hotel_id' => $hotel1->id,
            'name' => 'Deluxe Suite',
            'price_per_night' => 2500,
            'occupancy' => 2,
            'bed_type' => 'King'
        ]);

        $hotel2 = Hotel::create([
            'name' => 'Budget Inn',
            'description' => 'Affordable comfort',
            'address' => '45 Downtown St',
            'city' => 'Alexandria',
            'latitude' => 31.2001,
            'longitude' => 29.9187,
            'rating' => 3.5,
            'main_image' => 'budget_inn.jpg',
            'discount' => 0
        ]);

        Room::create([
            'hotel_id' => $hotel2->id,
            'name' => 'Standard Room',
            'price_per_night' => 800,
            'occupancy' => 2,
            'bed_type' => 'Queen'
        ]);

        Room::create([
            'hotel_id' => $hotel2->id,
            'name' => 'Zero Occupancy Room',
            'price_per_night' => 500,
            'occupancy' => 1,
            'bed_type' => 'Single'
        ]);
    }

    public function down(): void
    {
        // DB::table('hotels')->truncate();
    }
};
