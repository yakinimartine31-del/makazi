<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    public function index(Request $request): View
    {
        $ownerRoleId = Role::where('name', 'owner')->value('id');

        $stats = [
            'users' => User::count(),
            'owners' => $ownerRoleId ? User::whereHas('roles', fn ($query) => $query->whereKey($ownerRoleId))->count() : 0,
            'rooms' => Room::count(),
            'published' => Room::where('listing_status', 'published')->count(),
            'pending_approval' => Room::where('listing_status', 'pending_approval')->count(),
            'revenue' => Payment::where('status', 'paid')->sum('amount'),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];
        $pendingListings = Room::with(['owner', 'roomType', 'region', 'district', 'latestFee'])
            ->where('listing_status', 'pending_approval')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'pendingListings'));
    }
}
