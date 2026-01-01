<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('hotels')) {
            Schema::create('hotels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description');
                $table->string('address');
                $table->string('city')->index();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('rating', 3, 2)->default(0);
                $table->string('main_image')->nullable();
                $table->integer('discount')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
