<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            SettingsTableSeeder::class,
            RoomTypesTableSeeder::class,
            RegionsTableSeeder::class,
            DistrictsTableSeeder::class,
            WardsTableSeeder::class,
            AreasTableSeeder::class,
            AmenitiesTableSeeder::class,
        ]);
    }
}
