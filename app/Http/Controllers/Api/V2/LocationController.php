<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AreaResource;
use App\Http\Resources\V2\DistrictResource;
use App\Http\Resources\V2\RegionResource;
use App\Http\Resources\V2\WardResource;
use App\Models\Area;
use App\Models\District;
use App\Models\Region;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function regions(Request $request): JsonResponse
    {
        $regions = Region::query();

        if ($request->filled('search')) {
            $regions->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => RegionResource::collection($regions->get()),
        ]);
    }

    public function districts(Request $request, Region $region): JsonResponse
    {
        $districts = $region->districts();

        if ($request->filled('search')) {
            $districts->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => DistrictResource::collection($districts->get()),
        ]);
    }

    public function wards(Request $request, District $district): JsonResponse
    {
        $wards = $district->wards();

        if ($request->filled('search')) {
            $wards->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => WardResource::collection($wards->get()),
        ]);
    }

    public function areas(Request $request, Ward $ward): JsonResponse
    {
        $areas = $ward->areas();

        if ($request->filled('search')) {
            $areas->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => AreaResource::collection($areas->get()),
        ]);
    }
}
