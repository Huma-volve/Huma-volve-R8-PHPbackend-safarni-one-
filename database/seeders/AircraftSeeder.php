<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aircraft = [
            [
                'type' => 'Boeing 737-800',
                'total_seats' => 162,
                'seat_map_config' => [
                    'business' => [
                        'rows' => [1, 2, 3],
                        'columns' => ['A', 'B', 'E', 'F'],
                        'price_modifier' => 50000, // 500 EGP extra
                    ],
                    'economy' => [
                        'rows' => range(4, 28),
                        'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                        'price_modifier' => 0,
                    ],
                ],
            ],
            [
                'type' => 'Airbus A320',
                'total_seats' => 150,
                'seat_map_config' => [
                    'business' => [
                        'rows' => [1, 2],
                        'columns' => ['A', 'B', 'E', 'F'],
                        'price_modifier' => 45000,
                    ],
                    'economy' => [
                        'rows' => range(3, 27),
                        'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                        'price_modifier' => 0,
                    ],
                ],
            ],
            [
                'type' => 'Boeing 777-300ER',
                'total_seats' => 350,
                'seat_map_config' => [
                    'first' => [
                        'rows' => [1, 2],
                        'columns' => ['A', 'B', 'J', 'K'],
                        'price_modifier' => 150000,
                    ],
                    'business' => [
                        'rows' => range(3, 8),
                        'columns' => ['A', 'B', 'D', 'E', 'F', 'G', 'J', 'K'],
                        'price_modifier' => 80000,
                    ],
                    'economy' => [
                        'rows' => range(9, 45),
                        'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'],
                        'price_modifier' => 0,
                    ],
                ],
            ],
            [
                'type' => 'Airbus A380',
                'total_seats' => 500,
                'seat_map_config' => [
                    'first' => [
                        'rows' => [1, 2, 3],
                        'columns' => ['A', 'B', 'J', 'K'],
                        'price_modifier' => 200000,
                    ],
                    'business' => [
                        'rows' => range(4, 15),
                        'columns' => ['A', 'B', 'D', 'E', 'F', 'G', 'J', 'K'],
                        'price_modifier' => 100000,
                    ],
                    'economy' => [
                        'rows' => range(16, 60),
                        'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'],
                        'price_modifier' => 0,
                    ],
                ],
            ],
            [
                'type' => 'Boeing 787 Dreamliner',
                'total_seats' => 250,
                'seat_map_config' => [
                    'business' => [
                        'rows' => range(1, 6),
                        'columns' => ['A', 'B', 'D', 'E', 'F', 'G', 'H', 'K'],
                        'price_modifier' => 70000,
                    ],
                    'economy' => [
                        'rows' => range(7, 35),
                        'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K'],
                        'price_modifier' => 0,
                    ],
                ],
            ],
        ];

        foreach ($aircraft as $plane) {
            Aircraft::updateOrCreate(
                ['type' => $plane['type']],
                $plane
            );
        }
    }
}