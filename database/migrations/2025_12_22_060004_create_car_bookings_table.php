<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();

            // Rental Period
            $table->dateTime('pickup_datetime');
            $table->dateTime('dropoff_datetime');
            $table->unsignedInteger('total_hours');

            // Locations
            $table->string('pickup_location');
            $table->string('dropoff_location');

            // Driver Info
            $table->unsignedTinyInteger('driver_age');
            $table->string('driver_license');

            // Pricing
            $table->unsignedBigInteger('base_price');
            $table->unsignedBigInteger('extras_price')->default(0);
            $table->unsignedBigInteger('total_price');

            // Status
            $table->string('status')->default('pending'); // pending, confirmed, active, completed, cancelled

            $table->timestamps();

            // Indexes for performance
            $table->index(['car_id', 'pickup_datetime', 'dropoff_datetime']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_bookings');
    }
};
