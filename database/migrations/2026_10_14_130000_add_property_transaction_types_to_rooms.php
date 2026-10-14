<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('property_type')->default('room')->after('room_type_id');
            $table->string('transaction_type')->default('rent')->after('property_type');
            $table->decimal('sale_price', 15, 2)->nullable()->after('monthly_rent');
            $table->boolean('installment_allowed')->default(false)->after('sale_price');
            $table->foreignId('room_type_id')->nullable()->change();
            $table->decimal('monthly_rent', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['property_type', 'transaction_type', 'sale_price', 'installment_allowed']);
            $table->foreignId('room_type_id')->nullable(false)->change();
            $table->decimal('monthly_rent', 15, 2)->nullable(false)->change();
        });
    }
};
