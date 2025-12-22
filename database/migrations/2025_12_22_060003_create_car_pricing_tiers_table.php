<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('from_hours');
            $table->unsignedInteger('to_hours')->nullable();
            $table->unsignedBigInteger('price_per_hour');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_pricing_tiers');
    }
};
