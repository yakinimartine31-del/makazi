<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardsTableSeeder extends Seeder
{
    public function run(): void
    {
        $districtNames = District::all()->pluck('name')->toArray();

        $wardsByDistrict = [];

        foreach ($districtNames as $districtName) {
            $wardsByDistrict[$districtName] = [
                $districtName . ' A',
                $districtName . ' B',
                $districtName . ' Central',
            ];
        }

        foreach ($wardsByDistrict as $districtName => $wardNames) {
            $districtId = District::where('name', $districtName)->value('id');

            foreach ($wardNames as $wardName) {
                DB::table('wards')->insertOrIgnore([
                    'district_id' => $districtId,
                    'name'        => $wardName,
                    'description' => "Ward: $wardName, District: $districtName",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
