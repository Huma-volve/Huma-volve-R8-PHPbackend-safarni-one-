<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('hotels')->cascadeOnDelete();

            $table->string('name');
            $table->integer('price_per_night');
            $table->integer('occupancy');
            $table->string('bed_type');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
