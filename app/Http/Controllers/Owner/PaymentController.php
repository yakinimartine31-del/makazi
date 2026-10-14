<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\ListingFee;
use App\Models\Payment;
use App\Models\Room;
use App\Services\MockPaymentAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:owner,admin']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $roomIds = Room::where('owner_id', $user->id)->pluck('id');

        $payments = Payment::with(['room', 'listingFee', 'room.roomType'])
            ->whereIn('room_id', $roomIds)
            ->latest()
            ->paginate(10);
        $unpaidFees = ListingFee::with('room')
            ->whereIn('room_id', $roomIds)
            ->where('status', PaymentStatus::Unpaid)
            ->latest()
            ->get();

        $totalPaid = Payment::whereIn('room_id', $roomIds)
            ->where('status', PaymentStatus::Paid->value)
            ->sum('amount');
        $totalPending = Payment::whereIn('room_id', $roomIds)
            ->where('status', PaymentStatus::Pending->value)
            ->sum('amount');
        $totalFailed = Payment::whereIn('room_id', $roomIds)
            ->where('status', PaymentStatus::Failed->value)
            ->sum('amount');
        $totalUnpaid = ListingFee::whereIn('room_id', $roomIds)
            ->whereNotIn('status', [PaymentStatus::Paid->value, PaymentStatus::Waived->value])
            ->sum('amount');

        return view('owner.payments.index', compact(
            'payments', 'unpaidFees', 'totalPaid', 'totalPending', 'totalFailed', 'totalUnpaid'
        ));
    }

    public function pay(Request $request, ListingFee $listingFee): RedirectResponse
    {
        $user = $request->user();

        if ($listingFee->room->owner_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You can only pay fees for your own listings.');
        }

        if (in_array($listingFee->status, [PaymentStatus::Paid, PaymentStatus::Waived], true)) {
            return redirect()->route('owner.payments.index')
                ->with('status', 'This fee has already been settled.');
        }

        if ($listingFee->status !== PaymentStatus::Unpaid) {
            return back()->withErrors(['payment' => 'This listing fee is not currently payable.']);
        }

        $adapter = new MockPaymentAdapter;
        $payment = $adapter->simulateSuccess($listingFee);

        return redirect()->route('owner.listings.index')
            ->with('status', "Payment of {$payment->amount} completed. Submit the listing for approval when ready.");
    }
}
