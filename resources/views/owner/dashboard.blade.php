@extends('layouts.dashboard')

@section('title', 'Owner Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">Owner Dashboard</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">My Listings</h5>
                        <p class="h2 font-weight-bold text-primary mb-0">{{ $totalListings }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Published</h5>
                        <p class="h2 font-weight-bold text-success mb-0">{{ $published }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Pending Approval</h5>
                        <p class="h2 font-weight-bold text-warning mb-0">{{ $pendingApproval }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Unpaid Fees</h5>
                        <p class="h2 font-weight-bold text-danger mb-0">TZS {{ number_format($unpaidFees, 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Activity</span>
                <a href="{{ route('owner.listings.create') }}" class="btn btn-primary btn-sm">Create New Listing</a>
            </div>
            <div class="card-body">
                @if ($recentRooms->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-info-circle display-4 mb-3"></i>
                        <p class="mb-3">No listings yet. Create your first room listing to get started.</p>
                        <a href="{{ route('owner.listings.create') }}" class="btn btn-primary">Create Listing</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Fee Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRooms as $room)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($room->coverImage)
                                                    <img src="{{ Storage::disk($room->coverImage->disk)->url($room->coverImage->image_path) }}"
                                                         alt="" class="rounded mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded mr-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-house-door text-muted small"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold">{{ Str::limit($room->title, 35) }}</div>
                                                    <div class="text-muted small">{{ $room->region?->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>TZS {{ number_format((float) $room->listing_price, 0) }}{{ $room->transaction_type === 'rent' ? ' / month' : '' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $room->listing_status === 'published' ? 'success' : ($room->listing_status === 'pending_approval' ? 'warning' : 'secondary') }}">
                                                {{ \App\Enums\ListingStatus::from($room->listing_status)->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $fee = $room->latestFee;
                                                $status = $fee?->status;
                                            @endphp
                                            @if ($fee && $status)
                                                @php
                                                    $badge = in_array($status->value, ['paid', 'waived']) ? 'success' : 'warning';
                                                @endphp
                                                <span class="badge bg-{{ $badge }}">
                                                    {{ $status->label() }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">No fee</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right mt-3">
                        <a href="{{ route('owner.listings.index') }}" class="text-decoration-none">View all listings &rarr;</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection