<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $groups = DB::table('amenities')
                ->orderBy('id')
                ->get()
                ->groupBy(fn (object $amenity) => mb_strtolower(trim($amenity->name)));

            foreach ($groups as $amenities) {
                if ($amenities->count() < 2) {
                    continue;
                }

                $keeperId = $amenities->first()->id;

                foreach ($amenities->skip(1) as $duplicate) {
                    $roomLinks = DB::table('room_amenity')
                        ->where('amenity_id', $duplicate->id)
                        ->get(['id', 'room_id']);

                    foreach ($roomLinks as $link) {
                        $alreadyLinked = DB::table('room_amenity')
                            ->where('room_id', $link->room_id)
                            ->where('amenity_id', $keeperId)
                            ->exists();

                        if ($alreadyLinked) {
                            DB::table('room_amenity')->where('id', $link->id)->delete();
                        } else {
                            DB::table('room_amenity')
                                ->where('id', $link->id)
                                ->update(['amenity_id' => $keeperId]);
                        }
                    }

                    DB::table('amenities')->where('id', $duplicate->id)->delete();
                }
            }
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('room_amenity', function (Blueprint $table) {
            $table->unique(['room_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('room_amenity', function (Blueprint $table) {
            $table->dropUnique(['room_id', 'amenity_id']);
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
