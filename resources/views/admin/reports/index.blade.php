@extends('layouts.dashboard')

@section('title', 'Reports')

@section('content')
<h1 class="h3 mb-4">Reports</h1>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="small-box bg-primary mb-0">
            <div class="inner"><h3>{{ number_format($userCount) }}</h3><p>Total users</p></div>
            <div class="icon"><i class="bi bi-people"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="small-box bg-info mb-0">
            <div class="inner"><h3>{{ number_format($roomCount) }}</h3><p>Total listings</p></div>
            <div class="icon"><i class="bi bi-house-door"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="small-box bg-success mb-0">
            <div class="inner"><h3>TZS {{ number_format((float) $revenue, 0) }}</h3><p>Collected revenue</p></div>
            <div class="icon"><i class="bi bi-cash-stack"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h2 class="card-title mb-0">Listings by status</h2></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Status</th><th class="text-right">Listings</th></tr></thead>
                    <tbody>
                        @forelse ($listingStatuses as $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::headline($row->listing_status) }}</td>
                                <td class="text-right">{{ number_format($row->count) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-4 text-muted">No listings to report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h2 class="card-title mb-0">Payments by status</h2></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Status</th><th class="text-right">Count</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse ($paymentStatuses as $row)
                            <tr>
                                <td>{{ \App\Enums\PaymentStatus::tryFrom($row->status)?->label() ?? \Illuminate\Support\Str::headline($row->status) }}</td>
                                <td class="text-right">{{ number_format($row->count) }}</td>
                                <td class="text-right">TZS {{ number_format((float) $row->amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No payments to report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
