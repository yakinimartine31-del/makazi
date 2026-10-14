<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_performance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 32);
            $table->char('visitor_hash', 64);
            $table->date('event_date');
            $table->timestamps();
            $table->unique(['room_id', 'event_type', 'visitor_hash', 'event_date'], 'listing_performance_daily_unique');
        });

        Schema::create('listing_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->timestamps();
            $table->unique(['room_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_likes');
        Schema::dropIfExists('listing_performance_events');
    }
};
