@extends('layouts.dashboard')

@section('title', $room->title)

@section('content')
@php
    $status = \App\Enums\ListingStatus::from($room->listing_status);
    $feeStatus = $room->latestFee?->status;
    $canSubmit = in_array($room->listing_status, ['draft', 'rejected'], true)
        && in_array($feeStatus, [\App\Enums\PaymentStatus::Paid, \App\Enums\PaymentStatus::Waived], true);
    $canPay = $room->latestFee && $feeStatus === \App\Enums\PaymentStatus::Unpaid;
    $locationParts = array_filter([
        $room->area?->name,
        $room->ward?->name,
        $room->district?->name,
        $room->region?->name,
    ]);
    $mapQuery = is_numeric($room->latitude) && is_numeric($room->longitude)
        ? $room->latitude.','.$room->longitude
        : implode(', ', array_filter([$room->landmark, ...$locationParts, 'Tanzania']));
    $googleMapsUrl = 'https://www.google.com/maps?q='.rawurlencode($mapQuery);
    $publicUrl = $room->listing_status === 'published' ? route('listings.show', $room) : null;
@endphp

<link rel="stylesheet" href="{{ asset('css/owner-listing-detail.css') }}">

<div class="owner-listing-detail">
    <nav class="owner-listing-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('owner.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
        <a href="{{ route('owner.listings.index') }}">My Listings</a>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
        <span>{{ $room->title }}</span>
    </nav>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if ($room->listing_status === 'rejected' && $room->rejection_reason)
        <div class="owner-listing-notice owner-listing-notice--warning">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div><strong>Changes requested</strong><p>{{ $room->rejection_reason }}</p></div>
        </div>
    @elseif ($room->listing_status === 'pending_approval')
        <div class="owner-listing-notice owner-listing-notice--info">
            <i class="bi bi-hourglass-split" aria-hidden="true"></i>
            <div><strong>Awaiting admin review</strong><p>Your listing will be published after it is reviewed and approved.</p></div>
        </div>
    @endif

    <header class="owner-listing-header">
        <div>
            <div class="owner-listing-header-meta">
                <span class="owner-listing-reference">AURA-{{ str_pad((string) $room->id, 6, '0', STR_PAD_LEFT) }}</span>
                <span class="owner-listing-status owner-listing-status--{{ $room->listing_status }}">
                    <i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status->label() }}
                </span>
            </div>
            <h1>{{ $room->title }}</h1>
            <p><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ implode(', ', $locationParts) ?: 'Location not set' }}</p>
        </div>
        <div class="owner-listing-actions">
            @if ($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">
                    <i class="bi bi-box-arrow-up-right mr-1"></i>View public listing
                </a>
                <button type="button" class="btn btn-outline-secondary" data-share-listing
                    data-share-url="{{ $publicUrl }}" data-share-title="{{ $room->title }}">
                    <i class="bi bi-share mr-1" aria-hidden="true"></i>Share
                </button>
            @endif
            <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-primary">
                <i class="bi bi-pencil mr-1" aria-hidden="true"></i>Edit listing
            </a>
        </div>
    </header>
    <p class="owner-share-feedback" data-share-feedback role="status" aria-live="polite"></p>

    <section class="owner-listing-summary" aria-label="Listing summary">
        <div class="owner-listing-price">
            <span>{{ $room->transaction_type === 'sale' ? 'Sale price' : 'Rent' }}</span>
            <strong>TZS {{ number_format((float) $room->listing_price, 0) }}</strong>
            @if ($room->transaction_type === 'rent')
                <small>/ {{ $room->payment_frequency === 'monthly' ? 'month' : $room->payment_frequency }}</small>
            @endif
        </div>
        <div><span>Listing type</span><strong>{{ $room->transaction_type === 'sale' ? 'For sale' : 'For rent' }}</strong></div>
        <div><span>Property</span><strong>{{ $room->property_type_label }}{{ $room->roomType ? ' · '.$room->roomType->name : '' }}</strong></div>
        <div><span>Created</span><strong>{{ $room->created_at->format('M d, Y') }}</strong></div>
    </section>

    <section class="owner-listing-workflow" aria-label="Listing actions and payment status">
        <div class="owner-listing-fee">
            <span class="owner-listing-fee-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
            <div>
                <span>Listing fee</span>
                @if ($room->latestFee)
                    <strong>TZS {{ number_format((float) $room->latestFee->amount, 0) }}</strong>
                    <small class="owner-fee-status owner-fee-status--{{ $feeStatus->value }}">{{ $feeStatus->label() }}</small>
                @else
                    <strong>No fee recorded</strong>
                @endif
            </div>
        </div>

        <div class="owner-listing-next-action">
            @if ($canPay)
                <p>Pay the listing fee to continue with your submission.</p>
                <form method="POST" action="{{ route('owner.payments.pay', $room->latestFee) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-credit-card mr-1" aria-hidden="true"></i>Pay fee
                    </button>
                </form>
            @elseif ($canSubmit)
                <p>The fee is settled. Submit your listing for admin approval.</p>
                <form method="POST" action="{{ route('owner.listings.submit', $room) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send mr-1" aria-hidden="true"></i>Submit for approval
                    </button>
                </form>
            @elseif ($feeStatus === \App\Enums\PaymentStatus::Pending)
                <p>Your payment is processing. The submission action will be available when its status is updated.</p>
            @elseif ($room->listing_status === 'published')
                <p>Your listing is live and visible to people browsing AURA.</p>
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">
                    <i class="bi bi-eye mr-1" aria-hidden="true"></i>Open public listing
                </a>
            @elseif ($room->listing_status === 'pending_approval')
                <p>Your listing is in the admin review queue.</p>
            @else
                <p>Manage or update this listing using the actions above.</p>
            @endif
        </div>
    </section>

    <section class="owner-listing-panel owner-performance" aria-labelledby="owner-performance-heading">
        <div class="owner-listing-section-heading">
            <div><span>ANALYTICS</span><h2 id="owner-performance-heading">Listing Performance</h2></div>
            <small>Unique visitor activity per day</small>
        </div>
        <div class="owner-performance-grid">
            @foreach ([
                ['impression', 'Impressions', 'bi-eye'],
                ['view', 'Views', 'bi-house-check'],
                ['click', 'Clicks', 'bi-cursor'],
                ['like', 'Likes', 'bi-heart'],
            ] as [$metric, $label, $icon])
                <article class="owner-performance-stat">
                    <span class="owner-performance-stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                    <div><small>{{ $label }}</small><strong data-performance-value="{{ $metric }}">{{ number_format($performance[$metric]) }}</strong></div>
                </article>
            @endforeach
        </div>
        <h3 class="owner-performance-subheading">Lead Actions</h3>
        <div class="owner-performance-grid owner-performance-grid--leads">
            @foreach ([
                ['whatsapp_click', 'WhatsApp Clicks', 'bi-whatsapp'],
                ['call_click', 'Call Clicks', 'bi-telephone'],
                ['instagram_click', 'Instagram Clicks', 'bi-instagram'],
            ] as [$metric, $label, $icon])
                <article class="owner-performance-stat owner-performance-stat--lead">
                    <span class="owner-performance-stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                    <div><small>{{ $label }}</small><strong data-performance-value="{{ $metric }}">{{ number_format($performance[$metric]) }}</strong></div>
                </article>
            @endforeach
        </div>
        <p class="owner-performance-note">Instagram clicks will be counted when an Instagram contact link is connected to this listing.</p>
    </section>

    @if ($room->images->isNotEmpty())
        <section class="owner-listing-gallery-section">
            <div class="owner-listing-section-heading">
                <div><span>MEDIA</span><h2>Photos and videos</h2></div>
                <a href="{{ route('owner.listings.edit', $room) }}#images">Manage media</a>
            </div>
            <div class="owner-listing-gallery">
                @foreach ($room->images as $image)
                    @php
                        $extension = strtolower(pathinfo($image->image_path, PATHINFO_EXTENSION));
                        $isVideo = in_array($extension, ['mp4', 'webm', 'ogg'], true);
                    @endphp
                    <figure class="owner-listing-media {{ $image->is_cover ? 'owner-listing-media--cover' : '' }}">
                        @if ($isVideo)
                            <video src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->image_path) }}" controls preload="metadata"></video>
                        @else
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->image_path) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->image_path) }}" alt="{{ $room->title }} photo {{ $loop->iteration }}" loading="lazy">
                            </a>
                        @endif
                        @if ($image->is_cover)
                            <figcaption><i class="bi bi-star-fill" aria-hidden="true"></i> Cover photo</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </section>
    @else
        <div class="owner-listing-notice owner-listing-notice--info">
            <i class="bi bi-images" aria-hidden="true"></i>
            <div><strong>No listing photos yet</strong><p>Add photos to help people understand the property.</p></div>
            <a href="{{ route('owner.listings.edit', $room) }}#images" class="btn btn-sm btn-outline-primary">Add photos</a>
        </div>
    @endif

    <div class="owner-listing-detail-grid">
        <div class="owner-listing-main-column">
            <section class="owner-listing-panel">
                <div class="owner-listing-section-heading"><div><span>OVERVIEW</span><h2>Property details</h2></div></div>
                <p class="owner-listing-description">{{ $room->description }}</p>
                <dl class="owner-listing-facts">
                    @if ($room->rental_purpose)
                        <div><dt>Rental purpose</dt><dd>{{ $room->rental_purpose }}</dd></div>
                    @endif
                    @if ($room->room_size)
                        <div><dt>Property size</dt><dd>{{ $room->room_size }}</dd></div>
                    @endif
                    @if ($room->bedrooms !== null)
                        <div><dt>Bedrooms</dt><dd>{{ $room->bedrooms }}</dd></div>
                    @endif
                    @if ($room->bathroom_type)
                        <div><dt>Bathroom</dt><dd>{{ $room->bathroom_type }}{{ $room->bathroom_sharing && strtolower($room->bathroom_type) !== 'shared' ? ' · shared' : '' }}</dd></div>
                    @endif
                    @if ($room->kitchen_availability)
                        <div><dt>Kitchen</dt><dd>{{ $room->kitchen_availability }}</dd></div>
                    @endif
                    @if ($room->indoor_bathroom)
                        <div><dt>Indoor bathroom</dt><dd>Yes</dd></div>
                    @endif
                    @if ($room->transaction_type === 'rent')
                        @if ($room->deposit !== null)
                            <div><dt>Deposit</dt><dd>TZS {{ number_format((float) $room->deposit, 0) }}</dd></div>
                        @endif
                        <div><dt>Payment frequency</dt><dd>{{ ucfirst($room->payment_frequency) }}</dd></div>
                        @if ($room->minimum_rental_period)
                            <div><dt>Minimum rental period</dt><dd>{{ $room->minimum_rental_period }} {{ \Illuminate\Support\Str::plural('month', $room->minimum_rental_period) }}</dd></div>
                        @endif
                    @elseif ($room->installment_allowed)
                        <div><dt>Installments</dt><dd>Accepted</dd></div>
                    @endif
                    <div><dt>Price negotiable</dt><dd>{{ $room->negotiable ? 'Yes' : 'No' }}</dd></div>
                </dl>
                @if ($room->other_features)
                    <h3>Additional features</h3>
                    <p class="owner-listing-description">{{ $room->other_features }}</p>
                @endif
                @if ($room->additional_charges)
                    <h3>Additional charges</h3>
                    <p class="owner-listing-description">{{ $room->additional_charges }}</p>
                @endif
            </section>

            <section class="owner-listing-panel">
                <div class="owner-listing-section-heading">
                    <div><span>PROPERTY</span><h2>Amenities</h2></div>
                    <a href="{{ route('owner.listings.edit', $room) }}">Edit amenities</a>
                </div>
                @forelse ($room->amenities as $amenity)
                    <span class="owner-listing-amenity"><i class="bi {{ $amenity->icon ?: 'bi-check-circle' }}" aria-hidden="true"></i>{{ $amenity->name }}</span>
                @empty
                    <p class="text-muted mb-0">No amenities have been selected for this listing.</p>
                @endforelse
            </section>

            <section class="owner-listing-panel" id="location">
                <div class="owner-listing-section-heading"><div><span>LOCATION</span><h2>Property location</h2></div></div>
                <p class="owner-listing-address"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ implode(', ', $locationParts) ?: 'Location not set' }}</p>
                @if ($room->landmark)
                    <p class="text-muted">Nearby landmark: {{ $room->landmark }}</p>
                @endif
                @if (is_numeric($room->latitude) && is_numeric($room->longitude))
                    <p class="owner-listing-coordinates">Pinned coordinates: {{ $room->latitude }}, {{ $room->longitude }}</p>
                @endif
                <div class="owner-listing-map">
                    <iframe src="{{ $googleMapsUrl }}&output=embed" title="Google map for {{ $room->title }}"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
                <a href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="owner-listing-map-link">
                    <i class="bi bi-map" aria-hidden="true"></i>Open location in Google Maps
                </a>
            </section>

            @if ($room->payments->isNotEmpty())
                <section class="owner-listing-panel">
                    <div class="owner-listing-section-heading"><div><span>TRANSACTIONS</span><h2>Listing fee payments</h2></div></div>
                    <div class="table-responsive">
                        <table class="table table-sm owner-listing-payments-table">
                            <thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach ($room->payments as $payment)
                                    <tr>
                                        <td><code>{{ $payment->transaction_reference }}</code></td>
                                        <td>TZS {{ number_format((float) $payment->amount, 0) }}</td>
                                        <td>{{ $payment->status->label() }}</td>
                                        <td>{{ ($payment->paid_at ?? $payment->created_at)->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        <aside class="owner-listing-side-column">
            <section class="owner-listing-panel owner-listing-contact">
                <div class="owner-listing-section-heading"><div><span>CONTACT</span><h2>Listing contact</h2></div></div>
                @if ($room->contact_phone)
                    <strong>{{ $room->contact_name }}</strong>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $room->contact_phone) }}">
                        <i class="bi bi-telephone" aria-hidden="true"></i>{{ $room->contact_phone }}
                    </a>
                    @if ($room->whatsapp_phone)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $room->whatsapp_phone) }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>{{ $room->whatsapp_phone }}
                        </a>
                    @endif
                    <p>Preferred contact: {{ ucfirst($room->preferred_contact_method) }}</p>
                @else
                    <p class="text-muted">No contact details have been added.</p>
                @endif
                <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-outline-primary btn-block">
                    <i class="bi bi-pencil mr-1" aria-hidden="true"></i>Update contact details
                </a>
            </section>

            <section class="owner-listing-panel owner-listing-timeline">
                <div class="owner-listing-section-heading"><div><span>ACTIVITY</span><h2>Listing timeline</h2></div></div>
                <div><i class="bi bi-plus-circle" aria-hidden="true"></i><span><strong>Created</strong><small>{{ $room->created_at->format('M d, Y · h:i A') }}</small></span></div>
                @if ($room->approved_at)
                    <div><i class="bi bi-check-circle" aria-hidden="true"></i><span><strong>Approved</strong><small>{{ $room->approved_at->format('M d, Y · h:i A') }}</small></span></div>
                @endif
                @if ($room->published_at)
                    <div><i class="bi bi-globe" aria-hidden="true"></i><span><strong>Published</strong><small>{{ $room->published_at->format('M d, Y · h:i A') }}</small></span></div>
                @endif
                @if ($room->rejected_at)
                    <div><i class="bi bi-x-circle" aria-hidden="true"></i><span><strong>Rejected</strong><small>{{ $room->rejected_at->format('M d, Y · h:i A') }}</small></span></div>
                @endif
                <div><i class="bi bi-pencil-square" aria-hidden="true"></i><span><strong>Last updated</strong><small>{{ $room->updated_at->format('M d, Y · h:i A') }}</small></span></div>
            </section>

            <section class="owner-listing-panel owner-listing-manage">
                <div class="owner-listing-section-heading"><div><span>MANAGE</span><h2>Listing actions</h2></div></div>
                <a href="{{ route('owner.listings.edit', $room) }}" class="btn btn-outline-primary btn-block">
                    <i class="bi bi-pencil mr-1" aria-hidden="true"></i>Edit listing
                </a>
                <form method="POST" action="{{ route('owner.listings.destroy', $room) }}"
                    onsubmit="return confirm('Delete this listing permanently? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-block">
                        <i class="bi bi-trash mr-1" aria-hidden="true"></i>Delete listing
                    </button>
                </form>
                <a href="{{ route('owner.listings.index') }}" class="owner-listing-back-link">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>Back to My Listings
                </a>
            </section>
        </aside>
    </div>
</div>

<script>
    document.querySelectorAll('[data-share-listing]').forEach((button) => {
        button.addEventListener('click', async () => {
            const feedback = document.querySelector('[data-share-feedback]');
            const shareData = {
                title: button.dataset.shareTitle,
                url: button.dataset.shareUrl,
            };

            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                    feedback.textContent = 'Listing shared.';
                } else if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(shareData.url);
                    feedback.textContent = 'Listing link copied.';
                } else {
                    throw new Error('Sharing is not supported by this browser.');
                }
            } catch (error) {
                if (error.name === 'AbortError') return;
                console.error('Could not share listing:', error);
                feedback.textContent = 'Could not share the listing. Copy the URL from your browser address bar.';
            }
        });
    });
</script>
@endsection
