<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\ListingFee;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:owner,admin']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $totalListings = Room::where('owner_id', $user->id)->count();
        $published = Room::where('owner_id', $user->id)->where('listing_status', ListingStatus::Published->value)->count();
        $pendingApproval = Room::where('owner_id', $user->id)->where('listing_status', ListingStatus::PendingApproval->value)->count();
        $unpaidFees = ListingFee::whereIn('room_id', Room::where('owner_id', $user->id)->pluck('id'))
            ->whereNotIn('status', [PaymentStatus::Paid->value, PaymentStatus::Waived->value])
            ->sum('amount');

        $recentRooms = Room::with(['roomType', 'region', 'district', 'coverImage', 'latestFee'])
            ->where('owner_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('owner.dashboard', compact(
            'totalListings', 'published', 'pendingApproval', 'unpaidFees', 'recentRooms'
        ));
    }
}
