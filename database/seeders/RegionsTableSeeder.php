<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'Arusha', 'Manyara', 'Kilimanjaro', 'Tanga', 'Pwani',
            'Dar es Salaam', 'Mwanza', 'Kigoma', 'Katavi', 'Rukwa',
            'Nyerere', 'Songwe', 'Lindi', 'Mtwara', 'Ruvuma',
            'Iringa', 'Mbeya', 'Njombe', 'Singida', 'Tabora',
            'Shinyanga', 'Kagera', 'Simiyu', 'Haninge', 'Kibaha',
            'Bagamoyo', 'Chanzuru',
        ];

        foreach ($regions as $name) {
            DB::table('regions')->insertOrIgnore([
                'name' => $name,
                'description' => "Region: $name",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
