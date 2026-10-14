<x-app-layout>
    <x-slot name="title">
        {{ $room->title }}
    </x-slot>

    @php
        $images = $room->images->sortByDesc('is_cover')->values();
        $phone = trim((string) $room->contact_phone);
        $whatsapp = preg_replace('/\D+/', '', (string) ($room->whatsapp_phone ?: $room->contact_phone));
        if (str_starts_with($whatsapp, '0')) {
            $whatsapp = '255'.substr($whatsapp, 1);
        }
        $locationParts = array_filter([
            $room->area?->name,
            $room->ward?->name,
            $room->district?->name,
            $room->region?->name,
        ]);
        $propertyTypeLabel = [
            'room' => 'Chumba',
            'house' => 'Nyumba',
            'residential_plot' => 'Kiwanja cha makazi',
            'commercial_plot' => 'Kiwanja cha biashara',
            'farm' => 'Shamba',
            'commercial_space' => 'Eneo la biashara',
            'office' => 'Ofisi',
        ][$room->property_type] ?? $room->property_type_label;
        $hasCoordinates = is_numeric($room->latitude) && is_numeric($room->longitude);
        $mapQuery = $hasCoordinates
            ? $room->latitude.','.$room->longitude
            : implode(', ', array_filter([$room->landmark, ...$locationParts, 'Tanzania']));
        $googleMapsUrl = 'https://www.google.com/maps?q='.rawurlencode($mapQuery);
    @endphp

    <div class="aura-property-page">
        <nav class="aura-property-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Nyumbani</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('home', ['transaction_type' => $room->transaction_type]) }}">
                {{ $room->transaction_type === 'sale' ? 'Inauzwa' : 'Inapangishwa' }}
            </a>
            @if ($room->region)
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                <a href="{{ route('home', ['location' => $room->region->name, 'transaction_type' => $room->transaction_type]) }}">{{ $room->region->name }}</a>
            @endif
            @if ($room->district)
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                <span>{{ $room->district->name }}</span>
            @endif
        </nav>

        <div class="aura-property-columns">
            <article class="aura-property-main">
                <header class="aura-property-heading">
                    <div>
                        <h1>{{ $room->title }}</h1>
                        <p class="aura-property-reference">Namba ya tangazo: AURA-{{ str_pad((string) $room->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="aura-property-header-actions">
                        <span class="aura-property-status">{{ $room->transaction_type === 'sale' ? 'Inauzwa' : 'Inapangishwa' }}</span>
                        <button class="aura-property-like {{ $liked ? 'is-liked' : '' }}" type="button"
                            data-performance-like="{{ route('listings.performance.like', $room) }}"
                            aria-pressed="{{ $liked ? 'true' : 'false' }}" aria-label="{{ $liked ? 'Unlike listing' : 'Like listing' }}">
                            <i class="bi bi-heart{{ $liked ? '-fill' : '' }}" aria-hidden="true"></i>
                            <span data-like-count>{{ number_format($likesCount) }}</span>
                        </button>
                    </div>
                </header>

                <section class="aura-property-gallery" aria-label="Picha za tangazo">
                    @if ($images->isNotEmpty())
                        <div class="aura-gallery-stage">
                            <img src="{{ $images->first()->url }}" alt="{{ $room->title }}" class="aura-gallery-current" data-gallery-current>
                            @if ($images->count() > 1)
                                <button class="aura-gallery-arrow aura-gallery-arrow--previous" type="button" data-gallery-step="-1" aria-label="Previous photo">
                                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                </button>
                                <button class="aura-gallery-arrow aura-gallery-arrow--next" type="button" data-gallery-step="1" aria-label="Next photo">
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </button>
                                <span class="aura-gallery-count" data-gallery-count>1 / {{ $images->count() }}</span>
                            @endif
                        </div>
                        @if ($images->count() > 1)
                            <div class="aura-gallery-thumbnails" aria-label="Choose a photo">
                                @foreach ($images as $image)
                                    <button type="button" class="aura-gallery-thumbnail {{ $loop->first ? 'is-active' : '' }}"
                                        data-gallery-thumbnail="{{ $loop->index }}" aria-label="Show photo {{ $loop->iteration }}"
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                        <img src="{{ $image->url }}" alt="">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="aura-gallery-empty">
                            <i class="bi bi-house-door" aria-hidden="true"></i>
                            <span>Hakuna picha zilizowekwa kwenye tangazo hili.</span>
                        </div>
                    @endif
                </section>

                <section class="aura-property-summary" aria-label="Bei na eneo">
                    <div>
                        <span class="aura-property-location"><i class="bi bi-geo-alt" aria-hidden="true"></i>
                            {{ implode(', ', $locationParts) ?: 'Tanzania' }}
                            @if ($room->landmark) <span>· near {{ $room->landmark }}</span> @endif
                        </span>
                        <span class="aura-property-date">Imetangazwa {{ ($room->published_at ?? $room->created_at)->diffForHumans() }}</span>
                    </div>
                    <p class="aura-property-price">
                        TZS {{ number_format((float) $room->listing_price, 0) }}
                        @if ($room->transaction_type === 'rent')
                            <span>/ {{ $room->payment_frequency === 'monthly' ? 'mwezi' : $room->payment_frequency }}</span>
                        @endif
                    </p>
                </section>

                <section class="aura-property-facts" aria-label="Taarifa za nyumba">
                    <div><i class="bi bi-building" aria-hidden="true"></i><span><small>Aina ya nyumba</small><strong>{{ $propertyTypeLabel }}</strong></span></div>
                    @if ($room->bedrooms)
                        <div><i class="bi bi-door-open" aria-hidden="true"></i><span><small>Vyumba</small><strong>{{ $room->bedrooms }}</strong></span></div>
                    @endif
                    @if ($room->bathroom_type)
                        <div><i class="bi bi-droplet" aria-hidden="true"></i><span><small>Bafu</small><strong>{{ $room->bathroom_type }}</strong></span></div>
                    @endif
                    @if ($room->room_size)
                        <div><i class="bi bi-aspect-ratio" aria-hidden="true"></i><span><small>Ukubwa</small><strong>{{ $room->room_size }}</strong></span></div>
                    @endif
                    @if ($room->transaction_type === 'rent' && $room->deposit)
                        <div><i class="bi bi-wallet2" aria-hidden="true"></i><span><small>Amana</small><strong>TZS {{ number_format((float) $room->deposit, 0) }}</strong></span></div>
                    @endif
                    @if ($room->installment_allowed)
                        <div><i class="bi bi-check-circle" aria-hidden="true"></i><span><small>Malipo</small><strong>Malipo kwa awamu yanaruhusiwa</strong></span></div>
                    @endif
                    @if ($room->negotiable)
                        <div><i class="bi bi-chat-dots" aria-hidden="true"></i><span><small>Bei</small><strong>Bei inaweza kujadiliwa</strong></span></div>
                    @endif
                </section>

                @if ($room->amenities->isNotEmpty())
                    <section class="aura-property-section">
                        <h2>Huduma na sifa</h2>
                        <div class="aura-property-amenities">
                            @foreach ($room->amenities as $amenity)
                                <span><i class="bi {{ $amenity->icon ?: 'bi-check-circle' }}" aria-hidden="true"></i>{{ $amenity->name }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="aura-property-section aura-property-description">
                    <h2>Maelezo</h2>
                    <div class="aura-description-copy">{!! nl2br(e($room->description)) !!}</div>
                    @if ($room->other_features)
                        <h3>Sifa nyingine</h3>
                        <div class="aura-description-copy">{!! nl2br(e($room->other_features)) !!}</div>
                    @endif
                    @if ($room->additional_charges)
                        <h3>Gharama za ziada</h3>
                        <div class="aura-description-copy">{!! nl2br(e($room->additional_charges)) !!}</div>
                    @endif
                </section>

                <section class="aura-property-section aura-property-location-card">
                    <h2>Eneo</h2>
                    <p><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ implode(', ', $locationParts) ?: 'Tanzania' }}</p>
                    @if ($room->landmark)
                        <p class="text-muted mb-0">Alama ya karibu: {{ $room->landmark }}</p>
                    @endif
                    <div class="aura-property-map">
                        <iframe
                            src="{{ $googleMapsUrl }}&output=embed"
                            title="Ramani ya eneo la tangazo"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <a class="aura-map-directions" href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-map" aria-hidden="true"></i>
                        Fungua kwenye Google Maps
                    </a>
                </section>
            </article>

            <aside class="aura-contact-card" aria-label="Wasiliana na mmiliki wa tangazo">
                <div class="aura-contact-owner">
                    <div class="aura-owner-avatar" aria-hidden="true">{{ strtoupper(substr($room->contact_name ?: $room->owner?->name ?: 'A', 0, 1)) }}</div>
                    <h2>{{ $room->contact_name ?: $room->owner?->name ?: 'AURA listing owner' }}</h2>
                    <p>{{ $room->owner?->isOwner() ? 'Mmiliki wa nyumba' : 'Mawasiliano ya tangazo' }}</p>
                    @if ($room->owner?->email)
                        <a class="aura-owner-email" href="mailto:{{ $room->owner->email }}">{{ $room->owner->email }}</a>
                    @endif
                </div>
                <div class="aura-contact-actions">
                    @if ($phone !== '')
                        <details class="aura-contact-reveal">
                            <summary><i class="bi bi-telephone" aria-hidden="true"></i> Onyesha namba ya simu</summary>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"
                                data-performance-endpoint="{{ route('listings.performance.record', $room) }}" data-performance-event="call_click">{{ $phone }}</a>
                        </details>
                    @endif
                    @if ($whatsapp !== '')
                        <a class="aura-contact-whatsapp" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hello, I am interested in '.$room->title) }}" target="_blank" rel="noopener noreferrer"
                            data-performance-endpoint="{{ route('listings.performance.record', $room) }}" data-performance-event="whatsapp_click">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp
                        </a>
                    @endif
                    <a class="aura-contact-browse" href="{{ route('home', ['location' => $room->region?->name]) }}">
                        <i class="bi bi-grid" aria-hidden="true"></i> Angalia matangazo
                    </a>
                </div>
                <p class="aura-contact-safety"><i class="bi bi-shield-check" aria-hidden="true"></i> Tembelea nyumba na hakiki taarifa kabla ya kufanya malipo.</p>
            </aside>
        </div>

        @if ($relatedListings->isNotEmpty())
            <section class="aura-related-listings">
                <div class="aura-related-heading">
                    <div>
                        <span>Angalia zaidi</span>
                        <h2>Matangazo zaidi {{ $room->district?->name ?: $room->region?->name ?: 'Tanzania' }}</h2>
                    </div>
                    <a href="{{ route('home', ['location' => $room->region?->name]) }}">Angalia yote <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="aura-related-grid">
                    @foreach ($relatedListings as $related)
                        @php $relatedImage = $related->images->firstWhere('is_cover', true) ?? $related->images->first(); @endphp
                        <a class="aura-related-card" href="{{ route('listings.show', $related) }}"
                            data-performance-endpoint="{{ route('listings.performance.record', $related) }}" data-performance-event="click">
                            @if ($relatedImage)
                                <img src="{{ $relatedImage->url }}" alt="{{ $related->title }}" loading="lazy">
                            @else
                                <span class="aura-related-placeholder"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                            @endif
                            <span class="aura-related-card-content">
                                <span class="aura-related-location">{{ $related->district?->name ?: $related->region?->name ?: 'Tanzania' }}</span>
                                <strong>{{ $related->title }}</strong>
                                <span class="aura-related-price">TZS {{ number_format((float) $related->listing_price, 0) }}{{ $related->transaction_type === 'rent' ? ' / month' : '' }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

</x-app-layout>
