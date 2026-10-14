<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsTableSeeder extends Seeder
{
    public function run(): void
    {
        $districtsByRegion = [
            'Arusha'          => ['Arusha', 'Meru', 'Ngorongoro'],
            'Manyara'         => ['Manyara', 'Mto wa Mbu', 'Haninge'],
            'Kilimanjaro'     => ['Moshi', 'Same', 'Mkinga'],
            'Tanga'           => ['Tanga', 'Mkinga', 'Pangani'],
            'Pwani'           => ['Kisarawe', 'Rufiji', 'Kizwerengeni'],
            'Dar es Salaam'   => ['Kinondoni', 'Ilala', 'Temeke'],
            'Mwanza'          => ['Mwanza', 'Magu', 'Ilembe'],
            'Kigoma'          => ['Kigoma', 'Uvinza', 'Mtwara'],
            'Katavi'          => ['Mlele', 'Nsimbu'],
            'Rukwa'           => ['Sumbanzo', 'Mpanda'],
            'Nyerere'         => ['Nyerere', 'Mengi'],
            'Songwe'          => ['Songwe', 'Mlembate'],
            'Lindi'           => ['Lindi', 'Nachingwea'],
            'Mtwara'          => ['Mtwara', 'Newala', 'Tandahimbo'],
            'Ruvuma'          => ['Maseru', 'Nangwale', 'Tunduru'],
            'Iringa'          => ['Iringa', 'Kilolo'],
            'Mbeya'           => ['Mbeya', 'Mbarali', 'Kyela'],
            'Njombe'          => ['Njombe', 'Makete', 'Mufundi'],
            'Singida'         => ['Singida', 'Manyoni', 'Iramba'],
            'Tabora'          => ['Tabora', 'Uyi', 'Sikonge'],
            'Shinyanga'       => ['Shinyanga', 'Meatu', 'Kishapu'],
            'Kagera'          => ['Bukoba', 'Mutanda', 'Kagera'],
            'Simiyu'          => ['Simiyu', 'Bariadi', 'Manyoni'],
            'Haninge'         => ['Haninge', 'Mlandizi', 'Vwawa'],
            'Kibaha'          => ['Kibaha', 'Bahi', 'Kondoa'],
            'Bagamoyo'        => ['Bagamoyo', 'Kiwengwi', 'Mtwara'],
            'Chanzuru'        => ['Chanzuru', 'Mvomero', 'Kisarawe'],
        ];

        foreach ($districtsByRegion as $regionName => $districtNames) {
            $regionId = Region::where('name', $regionName)->value('id');

            foreach ($districtNames as $districtName) {
                DB::table('districts')->insertOrIgnore([
                    'region_id'   => $regionId,
                    'name'        => $districtName,
                    'description' => "District: $districtName, Region: $regionName",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
