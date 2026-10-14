@extends('layouts.dashboard')

@section('title', 'Manage Listings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Listings</h1>
    <a href="{{ route('owner.listings.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg mr-1"></i>Create Listing
    </a>
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif
@if ($errors->has('listing'))
    <div class="alert alert-danger">{{ $errors->first('listing') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.listings.index') }}" class="form-inline">
            <label for="status" class="mr-2">Filter by status</label>
            <select id="status" name="status" class="form-control form-control-sm mr-2">
                <option value="">All listings</option>
                @foreach (['draft', 'pending_payment', 'pending_approval', 'published', 'rejected', 'suspended', 'rented', 'unavailable'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ \App\Enums\ListingStatus::from($status)->label() }}
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
                        <th>Listing</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Listing status</th>
                        <th>Fee status</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        @php
                            $feeStatus = $room->latestFee?->status?->value;
                            $canApprove = $room->listing_status === 'pending_approval'
                                && ($room->fee_exempt || in_array($feeStatus, ['paid', 'waived'], true));
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('owner.listings.show', $room) }}" class="font-weight-bold">{{ $room->title }}</a>
                                <div class="small text-muted">{{ $room->property_type_label }}{{ $room->roomType ? ' · '.$room->roomType->name : '' }}</div>
                            </td>
                            <td>
                                {{ $room->owner?->name ?? '—' }}
                                <div class="small text-muted">{{ $room->owner?->email }}</div>
                            </td>
                            <td>{{ $room->region?->name ?? '—' }}{{ $room->district ? ', '.$room->district->name : '' }}</td>
                            <td class="text-nowrap">TZS {{ number_format((float) $room->listing_price, 0) }}{{ $room->transaction_type === 'rent' ? ' / month' : '' }}</td>
                            <td>{{ \App\Enums\ListingStatus::from($room->listing_status)->label() }}</td>
                            <td>{{ $room->latestFee?->status?->label() ?? 'No fee' }}</td>
                            <td class="text-nowrap">{{ $room->created_at->format('M d, Y') }}</td>
                            <td class="text-right text-nowrap">
                                @if ($room->listing_status === 'pending_approval')
                                    <form method="POST" action="{{ route('admin.listings.approve', $room) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit" @disabled(! $canApprove)
                                            title="{{ $canApprove ? 'Approve and publish listing' : 'Payment must be verified before approval' }}">
                                            Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" type="button"
                                        data-toggle="collapse" data-target="#reject-{{ $room->id }}">
                                        Reject
                                    </button>
                                    <form id="reject-{{ $room->id }}" method="POST"
                                        action="{{ route('admin.listings.reject', $room) }}" class="collapse mt-2">
                                        @csrf
                                        <label class="sr-only" for="reason-{{ $room->id }}">Rejection reason</label>
                                        <input id="reason-{{ $room->id }}" name="rejection_reason" class="form-control form-control-sm mb-1"
                                            placeholder="Reason for rejection" required maxlength="2000">
                                        <button class="btn btn-sm btn-danger" type="submit">Confirm rejection</button>
                                    </form>
                                @else
                                    <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endif
                                <form method="POST" action="{{ route('admin.listings.destroy', $room) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this listing permanently? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">No listings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($rooms->hasPages())
        <div class="card-footer">{{ $rooms->links() }}</div>
    @endif
</div>
@endsection
