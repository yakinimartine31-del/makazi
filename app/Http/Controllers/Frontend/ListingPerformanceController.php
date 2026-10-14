<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\ListingPerformanceTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingPerformanceController extends Controller
{
    public function record(Request $request, Room $room, ListingPerformanceTracker $tracker): JsonResponse
    {
        abort_unless($room->listing_status === 'published', 404);

        $validated = $request->validate([
            'event' => ['required', 'in:click,whatsapp_click,call_click,instagram_click'],
        ]);

        $tracker->record($room, $validated['event'], $request);

        return response()->json(['recorded' => true]);
    }

    public function toggleLike(Request $request, Room $room, ListingPerformanceTracker $tracker): JsonResponse
    {
        abort_unless($room->listing_status === 'published', 404);

        return response()->json($tracker->toggleLike($room, $request));
    }
}
