<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. OTP Codes
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            
            // Added purpose
            $table->string('purpose')->default('login'); 
            $table->boolean('used')->default(false); 
            
            $table->timestamp('expires_at')->nullable(); 
            $table->timestamps();
        });

        // 2. Social Identities
        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); 
            
            $table->string('provider_user_id'); 
            $table->string('email')->nullable(); 
            $table->json('profile_json')->nullable(); 
            
            $table->timestamps();
        });

        // 3. Reviews
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

        // 4. Favorites
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
        Schema::dropIfExists('social_identities');
        Schema::dropIfExists('otp_codes');
    }
};