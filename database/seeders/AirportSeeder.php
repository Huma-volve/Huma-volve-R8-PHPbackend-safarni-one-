<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airports = [
            // Egypt
            [
                'code' => 'CAI',
                'name' => 'Cairo International Airport',
                'city' => 'Cairo',
                'latitude' => 30.1219,
                'longitude' => 31.4056,
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'HRG',
                'name' => 'Hurghada International Airport',
                'city' => 'Hurghada',
                'latitude' => 27.1783,
                'longitude' => 33.7994,
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'SSH',
                'name' => 'Sharm El Sheikh International Airport',
                'city' => 'Sharm El Sheikh',
                'latitude' => 27.9773,
                'longitude' => 34.3950,
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'LXR',
                'name' => 'Luxor International Airport',
                'city' => 'Luxor',
                'latitude' => 25.6710,
                'longitude' => 32.7066,
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'ASW',
                'name' => 'Aswan International Airport',
                'city' => 'Aswan',
                'latitude' => 23.9644,
                'longitude' => 32.8200,
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'HBE',
                'name' => 'Borg El Arab Airport',
                'city' => 'Alexandria',
                'latitude' => 30.9177,
                'longitude' => 29.6964,
                'timezone' => 'Africa/Cairo',
            ],
            // International
            [
                'code' => 'DXB',
                'name' => 'Dubai International Airport',
                'city' => 'Dubai',
                'latitude' => 25.2532,
                'longitude' => 55.3657,
                'timezone' => 'Asia/Dubai',
            ],
            [
                'code' => 'JFK',
                'name' => 'John F. Kennedy International Airport',
                'city' => 'New York',
                'latitude' => 40.6413,
                'longitude' => -73.7781,
                'timezone' => 'America/New_York',
            ],
            [
                'code' => 'LHR',
                'name' => 'London Heathrow Airport',
                'city' => 'London',
                'latitude' => 51.4700,
                'longitude' => -0.4543,
                'timezone' => 'Europe/London',
            ],
            [
                'code' => 'CDG',
                'name' => 'Charles de Gaulle Airport',
                'city' => 'Paris',
                'latitude' => 49.0097,
                'longitude' => 2.5479,
                'timezone' => 'Europe/Paris',
            ],
            [
                'code' => 'FRA',
                'name' => 'Frankfurt Airport',
                'city' => 'Frankfurt',
                'latitude' => 50.0379,
                'longitude' => 8.5622,
                'timezone' => 'Europe/Berlin',
            ],
            [
                'code' => 'IST',
                'name' => 'Istanbul Airport',
                'city' => 'Istanbul',
                'latitude' => 41.2753,
                'longitude' => 28.7519,
                'timezone' => 'Europe/Istanbul',
            ],
            [
                'code' => 'RUH',
                'name' => 'King Khalid International Airport',
                'city' => 'Riyadh',
                'latitude' => 24.9576,
                'longitude' => 46.6988,
                'timezone' => 'Asia/Riyadh',
            ],
            [
                'code' => 'JED',
                'name' => 'King Abdulaziz International Airport',
                'city' => 'Jeddah',
                'latitude' => 21.6796,
                'longitude' => 39.1565,
                'timezone' => 'Asia/Riyadh',
            ],
            [
                'code' => 'AMM',
                'name' => 'Queen Alia International Airport',
                'city' => 'Amman',
                'latitude' => 31.7226,
                'longitude' => 35.9932,
                'timezone' => 'Asia/Amman',
            ],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(
                ['code' => $airport['code']],
                $airport
            );
        }
    }
}