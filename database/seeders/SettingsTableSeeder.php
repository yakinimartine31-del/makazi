<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key' => 'listing_fee_percentage',
            'type' => 'float',
            'value' => '5.00',
            'description' => 'The percentage fee charged to owners for listing a room.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
