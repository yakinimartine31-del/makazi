<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('additional_charges');
            $table->string('preferred_contact_method', 20)->nullable()->after('contact_name');
            $table->string('contact_phone', 30)->nullable()->after('preferred_contact_method');
            $table->string('whatsapp_phone', 30)->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'preferred_contact_method',
                'contact_phone',
                'whatsapp_phone',
            ]);
        });
    }
};
