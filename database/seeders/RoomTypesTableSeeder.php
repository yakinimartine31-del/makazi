<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Single Room', 'slug' => 'single-room', 'description' => 'A single occupancy room, typically with a bed, desk, and storage.'],
            ['name' => 'Self-Contained Room', 'slug' => 'self-contained', 'description' => 'A private room with its own bathroom and kitchenette.'],
            ['name' => 'Shared Room', 'slug' => 'shared-room', 'description' => 'A room shared by multiple tenants with shared facilities.'],
            ['name' => 'Master Room', 'slug' => 'master-room', 'description' => 'A large, luxurious room with private facilities.'],
            ['name' => 'Studio', 'slug' => 'studio', 'description' => 'A single-room living space with kitchen and bathroom.'],
            ['name' => 'Bedsit', 'slug' => 'bedsit', 'description' => 'A small, self-contained living unit with basic amenities.'],
        ];

        foreach ($types as $type) {
            DB::table('room_types')->insertOrIgnore($type + ['created_at' => now(), 'updated_at' => now()]);
        }
    }
}
