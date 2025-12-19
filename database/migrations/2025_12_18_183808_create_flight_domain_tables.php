<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Airports: Origin and Destination for flights
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique()->comment('IATA airport code');
            $table->string('name');
            $table->string('city');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone')->default('UTC');
            $table->timestamps();

            $table->index('city');
        });

        // 2. Airlines: Flight carriers
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->comment('IATA airline code');
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Aircraft: Plane types with seat configuration
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('e.g., Boeing 737, Airbus A320');
            $table->unsignedInteger('total_seats');
            $table->json('seat_map_config')->nullable()->comment('JSON seat layout');
            $table->timestamps();
        });

        // 4. Flights: Core flight entity
        Schema::create('flights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('flight_number', 10);
            $table->foreignId('airline_id')->constrained('airlines')->cascadeOnDelete();
            $table->foreignId('aircraft_id')->nullable()->constrained('aircraft')->nullOnDelete();
            $table->foreignId('origin_airport_id')->constrained('airports')->cascadeOnDelete();
            $table->foreignId('destination_airport_id')->constrained('airports')->cascadeOnDelete();
            $table->timestamp('departure_time');
            $table->timestamp('arrival_time');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedTinyInteger('stops')->default(0);
            $table->json('layover_details')->nullable();
            $table->text('baggage_rules')->nullable();
            $table->boolean('is_refundable')->default(false);
            $table->text('fare_conditions')->nullable();
            $table->unsignedBigInteger('base_price_egp')->comment('Price in piasters');
            $table->decimal('tax_percentage', 5, 2)->default(14.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['origin_airport_id', 'destination_airport_id', 'departure_time'], 'flight_search_index');
            $table->index('flight_number');
        });

        // 5. Seats: Seat inventory per flight
        Schema::create('seats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('flight_id');
            $table->string('class', 20)->comment('economy, business, first');
            $table->unsignedTinyInteger('row');
            $table->string('column', 2);
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('price_modifier_egp')->default(0)->comment('Extra cost in piasters');
            $table->timestamps();

            $table->foreign('flight_id')->references('id')->on('flights')->cascadeOnDelete();
            $table->unique(['flight_id', 'row', 'column']);
            $table->index(['flight_id', 'class', 'is_available']);
        });

        // 6. Passengers: Travel document data
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('title', 10)->comment('Mr, Mrs, Ms, Dr');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->text('passport_number')->comment('Encrypted at rest');
            $table->date('passport_expiry');
            $table->string('nationality', 2)->nullable()->comment('ISO country code');
            $table->text('special_requests')->nullable();
            $table->timestamps();

            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('flights');
        Schema::dropIfExists('aircraft');
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('airports');
    }
};