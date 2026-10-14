<div class="makazi-listing-grid">
    @foreach ($listings as $room)
        @php $coverImage = $room->images->firstWhere('is_cover', true) ?? $room->images->first(); @endphp
        <article class="makazi-listing-card">
            <a href="{{ route('listings.show', $room) }}" class="makazi-listing-image"
                data-performance-endpoint="{{ route('listings.performance.record', $room) }}" data-performance-event="click">
                @if ($coverImage)
                    <img src="{{ $coverImage->url }}" alt="{{ $room->title }}" loading="lazy">
                @else
                    <span class="makazi-listing-placeholder"><i class="bi bi-house-door"></i></span>
                @endif
                <span class="makazi-listing-badge">{{ $room->transaction_type === 'sale' ? 'Inauzwa' : 'Inapangishwa' }}</span>
            </a>
            <div class="makazi-listing-info">
                <span class="makazi-listing-location"><i class="bi bi-geo-alt"></i> {{ $room->region?->name ?? 'Tanzania' }}{{ $room->district ? ', '.$room->district->name : '' }}</span>
                <h3><a href="{{ route('listings.show', $room) }}"
                    data-performance-endpoint="{{ route('listings.performance.record', $room) }}" data-performance-event="click">{{ $room->title }}</a></h3>
                <div class="makazi-listing-meta">
                    <span><i class="bi bi-house"></i> {{ $room->property_type_label }}{{ $room->roomType ? ' · '.$room->roomType->name : '' }}</span>
                    @if ($room->bedrooms)<span><i class="bi bi-door-open"></i> {{ $room->bedrooms }} vyumba</span>@endif
                </div>
                <div class="makazi-listing-price">TZS {{ number_format((float) $room->listing_price, 0) }}@if ($room->transaction_type === 'rent')<small> / mwezi</small>@endif</div>
                <a href="{{ route('listings.show', $room) }}" class="makazi-listing-view"
                    data-performance-endpoint="{{ route('listings.performance.record', $room) }}" data-performance-event="click">Angalia nyumba <i class="bi bi-arrow-right"></i></a>
            </div>
        </article>
    @endforeach
</div>
