@extends('layouts.dashboard')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payments & Fees</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted small">Total Paid</h5>
                <p class="h2 font-weight-bold text-success mb-0">TZS {{ number_format($totalPaid, 0) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted small">Pending</h5>
                <p class="h2 font-weight-bold text-warning mb-0">TZS {{ number_format($totalPending, 0) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted small">Failed</h5>
                <p class="h2 font-weight-bold text-danger mb-0">TZS {{ number_format($totalFailed, 0) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-muted small">Unpaid Fees</h5>
                <p class="h2 font-weight-bold text-danger mb-0">TZS {{ number_format($totalUnpaid, 0) }}</p>
            </div>
        </div>
    </div>
</div>

@if ($errors->has('payment'))
    <div class="alert alert-danger">{{ $errors->first('payment') }}</div>
@endif
@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card mb-4">
    <div class="card-header">Unpaid Listing Fees</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Listing</th>
                        <th>Fee</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unpaidFees as $fee)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $fee->room->title }}</div>
                                <div class="text-muted small">{{ $fee->room->property_type_label }}</div>
                            </td>
                            <td>TZS {{ number_format((float) $fee->amount, 0) }}</td>
                            <td class="text-right">
                                <form action="{{ route('owner.payments.pay', $fee) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Pay TZS {{ number_format((float) $fee->amount, 0) }} now?');">
                                        Pay Now
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No unpaid listing fees.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Payment History
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Room</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Paid At</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td><code>{{ $payment->transaction_reference }}</code></td>
                            <td>
                                <div class="font-weight-bold">{{ Str::limit($payment->room->title ?? 'N/A', 30) }}</div>
                                <div class="text-muted small">{{ $payment->room?->roomType?->name }}</div>
                            </td>
                            <td>TZS {{ number_format($payment->amount, 0) }}</td>
                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === \App\Enums\PaymentStatus::Paid ? 'success' : ($payment->status === \App\Enums\PaymentStatus::Pending ? 'warning' : 'danger') }}">
                                    {{ $payment->status->label() }}
                                </span>
                            </td>
                            <td>{{ $payment->paid_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-right">
                                @php
                                    $fee = $payment->listingFee;
                                @endphp
                                @if ($fee && ! in_array($fee->status, [\App\Enums\PaymentStatus::Paid, \App\Enums\PaymentStatus::Waived], true))
                                    <form action="{{ route('owner.payments.pay', $fee) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Pay TZS {{ number_format($fee->amount, 0) }} now?');">
                                            Pay Now
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt display-4 mb-3 d-block"></i>
                                No payments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    @endif
</div>
</div>

@endsection