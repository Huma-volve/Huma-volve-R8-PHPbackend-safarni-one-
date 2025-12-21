<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. OTP Codes
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code', 4);
            $table->string('type'); // verification, password_reset
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            // Index for quick lookups
            $table->index(['email', 'type', 'is_used']);
        });

        // 2. Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->unsignedBigInteger('item_id');

            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->integer('rating')->default(0);
            $table->json('photos_json')->nullable();
            $table->string('status')->default('pending')->index();

            $table->timestamps();
        });

        // 3. Favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('otps');
    }
};
