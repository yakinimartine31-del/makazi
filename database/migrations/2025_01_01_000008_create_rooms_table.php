<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            // Basic information
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('rental_purpose')->nullable();

            // Room details
            $table->string('room_size')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->string('bathroom_type')->nullable();
            $table->boolean('bathroom_sharing')->default(false);
            $table->string('kitchen_availability')->nullable();
            $table->boolean('indoor_bathroom')->default(false);
            $table->text('other_features')->nullable();

            // Location
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Pricing
            $table->decimal('monthly_rent', 15, 2);
            $table->decimal('deposit', 15, 2)->nullable();
            $table->string('payment_frequency')->default('monthly');
            $table->integer('minimum_rental_period')->nullable();
            $table->boolean('negotiable')->default(false);
            $table->text('additional_charges')->nullable();

            // Status tracking
            $table->string('listing_status')->default('draft');
            $table->string('payment_status')->default('unpaid');

            // Fee and ownership tracking
            $table->boolean('fee_exempt')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('created_by_role')->nullable();

            // Publishing
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
