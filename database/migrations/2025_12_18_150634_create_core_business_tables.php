<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); 
            $table->string('title');
            $table->text('description')->nullable(); 
            $table->string('image')->nullable(); 
            $table->json('editable_fields')->nullable(); 
            $table->timestamps();
        });

        // 2. Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category'); 
            $table->foreign('category')->references('key')->on('categories')->cascadeOnUpdate();
            $table->unsignedBigInteger('item_id'); 
            $table->unsignedBigInteger('total_price'); 
            
            // Added payment_status
            $table->string('payment_status')->default('pending')->index();
            $table->string('status')->default('pending')->index(); 
            $table->timestamps();
            $table->index(['category', 'item_id']);
        });

        // 3. Booking Details
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->json('meta')->nullable(); 
            $table->timestamps();
        });

        // 4. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('amount'); 
            
            $table->string('currency')->default('EGP'); 
            
            $table->string('gateway'); 
            $table->string('transaction_id')->nullable()->index(); 
            $table->json('response_json')->nullable(); 
            $table->string('status')->default('pending')->index(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_details');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('categories');
    }
};