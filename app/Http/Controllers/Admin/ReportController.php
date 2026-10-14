<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $userCount = User::count();
        $roomCount = Room::count();
        $listingStatuses = Room::query()
            ->select('listing_status', DB::raw('COUNT(*) as count'))
            ->groupBy('listing_status')
            ->orderBy('listing_status')
            ->get();
        $paymentStatuses = Payment::query()
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        $revenue = Payment::where('status', 'paid')->sum('amount');

        return view('admin.reports.index', compact(
            'userCount', 'roomCount', 'listingStatuses', 'paymentStatuses', 'revenue'
        ));
    }
}
