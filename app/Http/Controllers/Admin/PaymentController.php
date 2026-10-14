<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', array_column(PaymentStatus::cases(), 'value'))],
        ]);

        $payments = Payment::with(['room.owner', 'listingFee'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Payment::query()
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('admin.payments.index', compact('payments', 'totals'));
    }
}
