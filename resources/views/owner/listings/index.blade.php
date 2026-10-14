@extends('layouts.dashboard')

@section('title', 'My Listings')

@php
    $roomsData = $rooms->map(static function ($room) {
        return [
            'id' => $room->id,
            'title' => $room->title,
            'images' => $room->images->sortBy('sort_order')->values()->map(static function ($image) {
                $extension = strtolower(pathinfo($image->image_path, PATHINFO_EXTENSION));

                return [
                    'url' => $image->url,
                    'ext' => $extension,
                    'type' => in_array($extension, ['mp4', 'webm', 'ogg'], true) ? 'video' : 'image',
                    'is_cover' => $image->is_cover,
                ];
            })->all(),
        ];
    })->values()->all();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Listings</h1>
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
        Your Room Listings
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Fee Status</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="position-relative mr-2" style="width: 48px; height: 48px;">
                                        @if ($room->images->isNotEmpty())
                                            @php
                                                $media = $room->images->sortBy('sort_order')->values();
                                                $firstMedia = $media->first();
                                                $ext = strtolower(pathinfo($firstMedia->image_path, PATHINFO_EXTENSION));
                                                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
                                            @endphp
                                             @if ($isVideo)
                                                 <video src="{{ $firstMedia->url }}"
                                                     class="rounded w-100 h-100 object-cover" muted playsinline></video>
                                             @else
                                                 <img src="{{ $firstMedia->url }}"
                                                     class="rounded w-100 h-100 object-cover" alt="">
                                             @endif
                                            @if ($media->count() > 1)
                                                <div class="position-absolute top-0 left-0 w-100 h-100 d-flex align-items-center justify-content-center text-white small" style="background-color: rgba(0, 0, 0, .25);">
                                                    +{{ $media->count() - 1 }} more
                                                </div>
                                            @endif
                                        @else
                                            <div class="bg-light rounded w-100 h-100 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-house-door text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">{{ Str::limit($room->title, 40) }}</div>
                                        <div class="text-muted small">{{ $room->property_type_label }}{{ $room->roomType ? ' · '.$room->roomType->name : '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $room->region?->name }}</div>
                                <div class="text-muted small">{{ $room->district?->name }}</div>
                            </td>
                            <td>TZS {{ number_format((float) $room->listing_price, 0) }}{{ $room->transaction_type === 'rent' ? ' / month' : '' }}</td>
                            <td>
                                <span class="badge bg-{{ $room->listing_status === 'published' ? 'success' : ($room->listing_status === 'pending_approval' ? 'warning' : ($room->listing_status === 'draft' ? 'secondary' : 'danger')) }}">
                                    {{ \App\Enums\ListingStatus::from($room->listing_status)->label() }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $fee = $room->latestFee;
                                    $feeStatus = $fee?->status;
                                @endphp
                                @if ($fee && $feeStatus)
                                    <span class="badge bg-{{ in_array($feeStatus->value, ['paid', 'waived']) ? 'success' : 'warning' }}">
                                        {{ $feeStatus->label() }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">No fee</span>
                                @endif
                            </td>
                            <td>{{ $room->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                @if (in_array($room->listing_status, ['draft', 'rejected'], true))
                                    @if ($feeStatus && in_array($feeStatus->value, ['paid', 'waived'], true))
                                        <form action="{{ route('owner.listings.submit', $room) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Submit for approval</button>
                                        </form>
                                    @else
                                        <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-warning">Pay fee</a>
                                    @endif
                                @endif
                                <a href="{{ route('owner.listings.show', $room) }}" class="btn btn-sm btn-outline-info">View</a>
                                <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('owner.listings.destroy', $room) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 mb-3 d-block"></i>
                                No listings yet.
                                <a href="{{ route('owner.listings.create') }}">Create your first listing</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($rooms->hasPages())
        <div class="card-footer">
            {{ $rooms->links() }}
        </div>
@endif
</div>

<script>
    const roomsData = {{ \Illuminate\Support\Js::from($roomsData) }};
    
    function showMediaModal(roomId) {
        const room = roomsData.find(r => r.id === roomId);
        if (!room) {
            console.error('Room not found:', roomId);
            return;
        }
        
        // Remove existing modal if any
        const existingModal = document.getElementById(`mediaModal-${roomId}`);
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal HTML
        let modalHtml = `
            <div class="modal fade" id="mediaModal-${roomId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${room.title}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div id="mediaCarousel-${roomId}" class="carousel slide" data-ride="carousel">
                                <div class="carousel-inner">
        `;
        
        room.images.forEach((image, index) => {
            const activeClass = index === 0 ? 'active' : '';
            if (image.type === 'video') {
                modalHtml += `
                                    <div class="carousel-item ${activeClass}">
                                         <video class="d-block w-100" controls>
                                             <source src="${image.url}" type="video/${image.ext}">
                                             Your browser does not support the video tag.
                                         </video>
                                    </div>
                                `;
            } else {
                modalHtml += `
                                    <div class="carousel-item ${activeClass}">
                                        <img src="${image.url}" class="d-block w-100" alt="Room image">
                                    </div>
                                `;
            }
        });
        
        modalHtml += `
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-target="#mediaCarousel-${roomId}" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-target="#mediaCarousel-${roomId}" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to DOM
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
        
        // Show modal
        $(`#mediaModal-${roomId}`).modal('show');
    }
</script>
@endsection