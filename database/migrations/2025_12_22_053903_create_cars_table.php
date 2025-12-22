<?php

use App\Enums\Availability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('type'); // sedan, suv, luxury
            $table->unsignedTinyInteger('seats');
            $table->string('location');

            // Pricing
            $table->unsignedBigInteger('price_per_hour');

            // Media & Info
            $table->string('image');
            $table->text('description');

            // Features (JSON: ['AC', 'GPS', 'Bluetooth'])
            $table->json('features')->nullable();

            // Status
            $table->string('availability')->default(Availability::Available->value);
            $table->decimal('rating', 3, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['location', 'availability']);
            $table->index(['brand', 'seats', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
