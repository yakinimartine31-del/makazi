<?php

namespace Database\Seeders;

use App\Models\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasTableSeeder extends Seeder
{
    public function run(): void
    {
        $wards = Ward::all()->pluck('name')->toArray();

        foreach ($wards as $wardName) {
            $areas = [
                $wardName . ' Area 1',
                $wardName . ' Area 2',
            ];

            $wardId = Ward::where('name', $wardName)->value('id');

            foreach ($areas as $areaName) {
                DB::table('areas')->insertOrIgnore([
                    'ward_id'     => $wardId,
                    'name'        => $areaName,
                    'description' => "Area: $areaName, Ward: $wardName",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
