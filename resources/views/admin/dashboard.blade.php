@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Admin Dashboard</h1>
    <a href="{{ route('admin.listings.index') }}" class="btn btn-outline-primary">Manage listings</a>
</div>

<div class="row mb-4">
    @foreach ([
        ['Users', number_format($stats['users']), 'primary', 'people'],
        ['Owners', number_format($stats['owners']), 'info', 'person-badge'],
        ['Rooms', number_format($stats['rooms']), 'success', 'house-door'],
        ['Published', number_format($stats['published']), 'success', 'check-circle'],
        ['Pending Approval', number_format($stats['pending_approval']), 'warning', 'hourglass-split'],
        ['Platform Revenue', 'TZS '.number_format($stats['revenue'], 0), 'success', 'cash-stack'],
        ['Failed Payments', number_format($stats['failed_payments']), 'danger', 'exclamation-triangle'],
    ] as [$label, $value, $color, $icon])
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-{{ $color }} mb-0">
                <div class="inner">
                    <h3>{{ $value }}</h3>
                    <p>{{ $label }}</p>
                </div>
                <div class="icon"><i class="bi bi-{{ $icon }}"></i></div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0">Pending Approvals</h2>
        <a href="{{ route('admin.listings.index', ['status' => 'pending_approval']) }}">View all</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Fee</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingListings as $room)
                        <tr>
                            <td><a href="{{ route('owner.listings.show', $room) }}">{{ $room->title }}</a></td>
                            <td>{{ $room->owner?->name ?? '—' }}</td>
                            <td>{{ $room->region?->name ?? '—' }}{{ $room->district ? ', '.$room->district->name : '' }}</td>
                            <td>TZS {{ number_format((float) $room->listing_price, 0) }}{{ $room->transaction_type === 'rent' ? ' / month' : '' }}</td>
                            <td>{{ $room->latestFee?->status?->label() ?? 'No fee' }}</td>
                            <td>{{ $room->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No listings awaiting approval.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
