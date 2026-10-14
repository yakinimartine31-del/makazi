<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\ListingPerformanceTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function show(Request $request, Room $room, ListingPerformanceTracker $performanceTracker): View
    {
        abort_unless($room->listing_status === 'published', 404);

        $performanceTracker->record($room, 'view', $request);

        $room->load([
            'owner',
            'roomType',
            'region',
            'district',
            'ward',
            'area',
            'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'amenities',
        ]);

        $relatedListings = Room::with(['region', 'district', 'roomType', 'images'])
            ->where('listing_status', 'published')
            ->where('id', '!=', $room->id)
            ->where('region_id', $room->region_id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        $visitorIdentity = $request->user()
            ? 'user:'.$request->user()->getAuthIdentifier()
            : 'session:'.$request->session()->getId();
        $liked = DB::table('listing_likes')
            ->where('room_id', $room->id)
            ->where('visitor_hash', hash_hmac('sha256', $visitorIdentity, (string) config('app.key')))
            ->exists();
        $likesCount = $performanceTracker->likeCount($room);

        return view('rooms.show', compact('room', 'relatedListings', 'liked', 'likesCount'));
    }
}
