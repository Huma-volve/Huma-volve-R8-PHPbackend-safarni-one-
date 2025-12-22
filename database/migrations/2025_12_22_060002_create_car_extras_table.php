<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_extras', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // GPS, Child Seat, Insurance
            $table->enum('pricing_type', ['per_rental', 'per_day']);
            $table->unsignedBigInteger('price');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_extras');
    }
};
