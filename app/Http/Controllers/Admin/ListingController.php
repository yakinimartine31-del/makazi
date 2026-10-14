<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Room;
use App\Services\ListingFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:draft,pending_payment,pending_approval,published,rejected,suspended,rented,unavailable'],
        ]);

        $rooms = Room::with(['owner', 'roomType', 'region', 'district', 'latestFee'])
            ->when($request->filled('status'), fn ($query) => $query->where('listing_status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.listings.index', compact('rooms'));
    }

    public function approve(Request $request, Room $room, ListingFeeService $listingFeeService): RedirectResponse
    {
        if (! $listingFeeService->canPublish($room)) {
            return back()->withErrors([
                'listing' => 'Only listings awaiting approval with verified payment or an approved fee waiver can be published.',
            ]);
        }

        DB::transaction(function () use ($room, $request): void {
            $room->update([
                'listing_status' => 'published',
                'approved_at' => now(),
                'published_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'listing.approved',
                'model_type' => Room::class,
                'model_id' => $room->id,
                'details' => ['title' => $room->title],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Listing approved and published.');
    }

    public function reject(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        if ($room->listing_status !== 'pending_approval') {
            return back()->withErrors([
                'listing' => 'Only listings awaiting approval can be rejected.',
            ]);
        }

        DB::transaction(function () use ($room, $request, $validated): void {
            $room->update([
                'listing_status' => 'rejected',
                'rejected_at' => now(),
                'published_at' => null,
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'listing.rejected',
                'model_type' => Room::class,
                'model_id' => $room->id,
                'details' => ['title' => $room->title, 'reason' => $validated['rejection_reason']],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Listing rejected.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        DB::transaction(function () use ($room, $request): void {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'listing.deleted',
                'model_type' => Room::class,
                'model_id' => $room->id,
                'details' => ['title' => $room->title],
                'ip_address' => $request->ip(),
            ]);

            $room->delete();
        });

        return back()->with('status', 'Listing deleted.');
    }
}
