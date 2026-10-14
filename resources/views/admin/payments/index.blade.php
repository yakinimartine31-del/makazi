@extends('layouts.dashboard')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payments</h1>
</div>

<div class="row mb-4">
    @foreach (\App\Enums\PaymentStatus::cases() as $status)
        @php $total = $totals->get($status->value); @endphp
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-light mb-0">
                <div class="inner">
                    <h3>{{ number_format($total?->count ?? 0) }}</h3>
                    <p>{{ $status->label() }} · TZS {{ number_format((float) ($total?->amount ?? 0), 0) }}</p>
                </div>
                <div class="icon"><i class="bi bi-credit-card"></i></div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="form-inline">
            <label for="status" class="mr-2">Filter by status</label>
            <select id="status" name="status" class="form-control form-control-sm mr-2">
                <option value="">All payments</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Listing</th>
                        <th>Owner</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->transaction_reference }}</td>
                            <td>
                                <a href="{{ route('owner.listings.show', $payment->room) }}">{{ $payment->room?->title ?? 'Deleted listing' }}</a>
                            </td>
                            <td>{{ $payment->room?->owner?->name ?? '—' }}</td>
                            <td class="text-nowrap">TZS {{ number_format((float) $payment->amount, 0) }}</td>
                            <td>{{ $payment->status?->label() ?? '—' }}</td>
                            <td>{{ $payment->payment_method ?? '—' }}</td>
                            <td class="text-nowrap">{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($payments->hasPages())
        <div class="card-footer">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
