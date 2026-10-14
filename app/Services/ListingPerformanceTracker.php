<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingPerformanceTracker
{
    public function record(Room $room, string $eventType, Request $request): void
    {
        DB::table('listing_performance_events')->insertOrIgnore([
            'room_id' => $room->id,
            'event_type' => $eventType,
            'visitor_hash' => $this->visitorHash($request),
            'event_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function toggleLike(Room $room, Request $request): array
    {
        $visitorHash = $this->visitorHash($request);
        $like = DB::table('listing_likes')
            ->where('room_id', $room->id)
            ->where('visitor_hash', $visitorHash);

        if ($like->exists()) {
            $like->delete();

            return ['liked' => false, 'likes' => $this->likeCount($room)];
        }

        DB::table('listing_likes')->insert([
            'room_id' => $room->id,
            'visitor_hash' => $visitorHash,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['liked' => true, 'likes' => $this->likeCount($room)];
    }

    public function likeCount(Room $room): int
    {
        return DB::table('listing_likes')->where('room_id', $room->id)->count();
    }

    private function visitorHash(Request $request): string
    {
        $visitorIdentity = $request->user()
            ? 'user:'.$request->user()->getAuthIdentifier()
            : 'session:'.$request->session()->getId();

        return hash_hmac('sha256', $visitorIdentity, (string) config('app.key'));
    }
}
