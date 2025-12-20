<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\Seat;
use App\Models\Airport;
use App\Models\Airline;
use App\Models\Aircraft;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $airports = Airport::all()->keyBy('code');
        $airlines = Airline::all()->keyBy('code');
        $aircraft = Aircraft::all();

        if ($airports->isEmpty() || $airlines->isEmpty() || $aircraft->isEmpty()) {
            $this->command->warn('Please run AirportSeeder, AirlineSeeder, and AircraftSeeder first.');
            return;
        }

        $flights = [
            // Cairo to Dubai
            [
                'flight_number' => 'MS901',
                'airline_code' => 'MS',
                'aircraft_type' => 'Boeing 737-800',
                'origin' => 'CAI',
                'destination' => 'DXB',
                'departure_offset_days' => 1,
                'departure_time' => '08:00',
                'duration_minutes' => 180,
                'stops' => 0,
                'base_price_egp' => 850000, // 8,500 EGP
                'is_refundable' => true,
            ],
            [
                'flight_number' => 'EK926',
                'airline_code' => 'EK',
                'aircraft_type' => 'Boeing 777-300ER',
                'origin' => 'CAI',
                'destination' => 'DXB',
                'departure_offset_days' => 1,
                'departure_time' => '14:30',
                'duration_minutes' => 185,
                'stops' => 0,
                'base_price_egp' => 950000, // 9,500 EGP
                'is_refundable' => true,
            ],
            // Cairo to London
            [
                'flight_number' => 'MS777',
                'airline_code' => 'MS',
                'aircraft_type' => 'Boeing 787 Dreamliner',
                'origin' => 'CAI',
                'destination' => 'LHR',
                'departure_offset_days' => 2,
                'departure_time' => '10:00',
                'duration_minutes' => 300,
                'stops' => 0,
                'base_price_egp' => 1500000, // 15,000 EGP
                'is_refundable' => false,
            ],
            [
                'flight_number' => 'BA154',
                'airline_code' => 'BA',
                'aircraft_type' => 'Airbus A320',
                'origin' => 'CAI',
                'destination' => 'LHR',
                'departure_offset_days' => 2,
                'departure_time' => '16:00',
                'duration_minutes' => 310,
                'stops' => 0,
                'base_price_egp' => 1650000, // 16,500 EGP
                'is_refundable' => true,
            ],
            // Cairo to Jeddah
            [
                'flight_number' => 'MS631',
                'airline_code' => 'MS',
                'aircraft_type' => 'Boeing 737-800',
                'origin' => 'CAI',
                'destination' => 'JED',
                'departure_offset_days' => 1,
                'departure_time' => '06:00',
                'duration_minutes' => 120,
                'stops' => 0,
                'base_price_egp' => 650000, // 6,500 EGP
                'is_refundable' => true,
            ],
            [
                'flight_number' => 'SV302',
                'airline_code' => 'SV',
                'aircraft_type' => 'Airbus A320',
                'origin' => 'CAI',
                'destination' => 'JED',
                'departure_offset_days' => 1,
                'departure_time' => '22:00',
                'duration_minutes' => 125,
                'stops' => 0,
                'base_price_egp' => 700000, // 7,000 EGP
                'is_refundable' => false,
            ],
            // Cairo to Istanbul
            [
                'flight_number' => 'TK695',
                'airline_code' => 'TK',
                'aircraft_type' => 'Airbus A320',
                'origin' => 'CAI',
                'destination' => 'IST',
                'departure_offset_days' => 3,
                'departure_time' => '09:00',
                'duration_minutes' => 150,
                'stops' => 0,
                'base_price_egp' => 750000, // 7,500 EGP
                'is_refundable' => true,
            ],
            // Cairo to Paris
            [
                'flight_number' => 'AF503',
                'airline_code' => 'AF',
                'aircraft_type' => 'Boeing 787 Dreamliner',
                'origin' => 'CAI',
                'destination' => 'CDG',
                'departure_offset_days' => 4,
                'departure_time' => '11:00',
                'duration_minutes' => 280,
                'stops' => 0,
                'base_price_egp' => 1400000, // 14,000 EGP
                'is_refundable' => true,
            ],
            // Domestic: Cairo to Sharm El Sheikh
            [
                'flight_number' => 'MS137',
                'airline_code' => 'MS',
                'aircraft_type' => 'Boeing 737-800',
                'origin' => 'CAI',
                'destination' => 'SSH',
                'departure_offset_days' => 1,
                'departure_time' => '07:00',
                'duration_minutes' => 60,
                'stops' => 0,
                'base_price_egp' => 250000, // 2,500 EGP
                'is_refundable' => true,
            ],
            [
                'flight_number' => 'MS139',
                'airline_code' => 'MS',
                'aircraft_type' => 'Airbus A320',
                'origin' => 'CAI',
                'destination' => 'SSH',
                'departure_offset_days' => 1,
                'departure_time' => '18:00',
                'duration_minutes' => 60,
                'stops' => 0,
                'base_price_egp' => 280000, // 2,800 EGP
                'is_refundable' => false,
            ],
            // Domestic: Cairo to Hurghada
            [
                'flight_number' => 'MS307',
                'airline_code' => 'MS',
                'aircraft_type' => 'Boeing 737-800',
                'origin' => 'CAI',
                'destination' => 'HRG',
                'departure_offset_days' => 1,
                'departure_time' => '08:30',
                'duration_minutes' => 55,
                'stops' => 0,
                'base_price_egp' => 230000, // 2,300 EGP
                'is_refundable' => true,
            ],
            // Domestic: Cairo to Luxor
            [
                'flight_number' => 'MS063',
                'airline_code' => 'MS',
                'aircraft_type' => 'Airbus A320',
                'origin' => 'CAI',
                'destination' => 'LXR',
                'departure_offset_days' => 2,
                'departure_time' => '06:30',
                'duration_minutes' => 70,
                'stops' => 0,
                'base_price_egp' => 270000, // 2,700 EGP
                'is_refundable' => true,
            ],
            // With stops: Cairo to New York via Dubai
            [
                'flight_number' => 'EK927',
                'airline_code' => 'EK',
                'aircraft_type' => 'Airbus A380',
                'origin' => 'CAI',
                'destination' => 'JFK',
                'departure_offset_days' => 3,
                'departure_time' => '02:00',
                'duration_minutes' => 960, // 16 hours total
                'stops' => 1,
                'layover_details' => [
                    ['airport' => 'DXB', 'duration_minutes' => 180],
                ],
                'base_price_egp' => 3500000, // 35,000 EGP
                'is_refundable' => true,
            ],
            // With stops: Cairo to Frankfurt via Istanbul
            [
                'flight_number' => 'TK697',
                'airline_code' => 'TK',
                'aircraft_type' => 'Boeing 777-300ER',
                'origin' => 'CAI',
                'destination' => 'FRA',
                'departure_offset_days' => 5,
                'departure_time' => '07:00',
                'duration_minutes' => 420, // 7 hours total
                'stops' => 1,
                'layover_details' => [
                    ['airport' => 'IST', 'duration_minutes' => 120],
                ],
                'base_price_egp' => 1200000, // 12,000 EGP
                'is_refundable' => false,
            ],
        ];

        foreach ($flights as $flightData) {
            $airline = $airlines->get($flightData['airline_code']);
            $aircraftModel = $aircraft->where('type', $flightData['aircraft_type'])->first();
            $origin = $airports->get($flightData['origin']);
            $destination = $airports->get($flightData['destination']);

            if (!$airline || !$aircraftModel || !$origin || !$destination) {
                continue;
            }

            $departureDate = now()->addDays($flightData['departure_offset_days']);
            $departureTime = \Carbon\Carbon::parse($departureDate->format('Y-m-d') . ' ' . $flightData['departure_time']);
            $arrivalTime = $departureTime->copy()->addMinutes($flightData['duration_minutes']);

            $flight = Flight::create([
                'id' => Str::uuid(),
                'flight_number' => $flightData['flight_number'],
                'airline_id' => $airline->id,
                'aircraft_id' => $aircraftModel->id,
                'origin_airport_id' => $origin->id,
                'destination_airport_id' => $destination->id,
                'departure_time' => $departureTime,
                'arrival_time' => $arrivalTime,
                'duration_minutes' => $flightData['duration_minutes'],
                'stops' => $flightData['stops'],
                'layover_details' => $flightData['layover_details'] ?? null,
                'baggage_rules' => '1x23kg checked, 1x7kg cabin',
                'is_refundable' => $flightData['is_refundable'],
                'fare_conditions' => $flightData['is_refundable']
                    ? 'Free cancellation up to 24 hours before departure'
                    : 'Non-refundable ticket. Changes allowed with fee.',
                'base_price_egp' => $flightData['base_price_egp'],
                'tax_percentage' => 14.00,
                'is_active' => true,
            ]);

            // Create seats for this flight
            $this->createSeatsForFlight($flight, $aircraftModel);
        }

        $this->command->info('Created ' . Flight::count() . ' flights with seats.');
    }

    /**
     * Create seats for a flight based on aircraft configuration.
     */
    protected function createSeatsForFlight(Flight $flight, Aircraft $aircraft): void
    {
        $seatConfig = $aircraft->seat_map_config;

        if (!$seatConfig) {
            return;
        }

        foreach ($seatConfig as $class => $config) {
            $rows = $config['rows'];
            $columns = $config['columns'];
            $priceModifier = $config['price_modifier'] ?? 0;

            foreach ($rows as $row) {
                foreach ($columns as $column) {
                    Seat::create([
                        'id' => Str::uuid(),
                        'flight_id' => $flight->id,
                        'class' => $class,
                        'row' => $row,
                        'column' => $column,
                        'is_available' => true,
                        'price_modifier_egp' => $priceModifier,
                    ]);
                }
            }
        }
    }
}