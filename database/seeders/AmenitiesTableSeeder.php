<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitiesTableSeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Water', 'icon' => 'bi-water'],
            ['name' => 'Electricity', 'icon' => 'bi-lightning-charge'],
            ['name' => 'Wi-Fi', 'icon' => 'bi-wifi'],
            ['name' => 'Private Bathroom', 'icon' => 'bi-door-closed'],
            ['name' => 'Shared Bathroom', 'icon' => 'bi-people'],
            ['name' => 'Kitchen', 'icon' => 'bi-stove'],
            ['name' => 'Laundry Facilities', 'icon' => 'bi-washing-machine'],
            ['name' => 'Garbage Collection', 'icon' => 'bi-trash'],
            ['name' => 'Balcony', 'icon' => 'bi-door-open'],
            ['name' => 'Air Conditioning', 'icon' => 'bi-snow'],
            ['name' => 'Parking Space', 'icon' => 'bi-car-front'],
            ['name' => 'Security Guard', 'icon' => 'bi-shield-lock'],
        ];

        foreach ($amenities as $amenity) {
            DB::table('amenities')->insertOrIgnore($amenity + ['description' => null, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
