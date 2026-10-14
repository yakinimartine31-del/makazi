<x-app-layout>
    <x-slot name="title">
        Nyumba za kupanga Tanzania
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    <section class="makazi-hero">
        <div class="makazi-city" aria-hidden="true">
            <span style="--height: 48%; --width: 9%; --left: 0%"></span>
            <span style="--height: 72%; --width: 12%; --left: 8%"></span>
            <span style="--height: 57%; --width: 10%; --left: 20%"></span>
            <span style="--height: 84%; --width: 13%; --left: 31%"></span>
            <span style="--height: 62%; --width: 11%; --left: 45%"></span>
            <span style="--height: 76%; --width: 12%; --left: 56%"></span>
            <span style="--height: 52%; --width: 10%; --left: 68%"></span>
            <span style="--height: 88%; --width: 14%; --left: 78%"></span>
            <span style="--height: 58%; --width: 12%; --left: 91%"></span>
        </div>

        <div class="makazi-hero-content">
            <span class="makazi-eyebrow">AURA · TANZANIA</span>
            <h1>Tafuta nyumba<br><span>inayokufaa.</span></h1>
            <p>Gundua vyumba na nyumba za kupanga kote Tanzania.</p>

            <form class="makazi-search" method="GET" action="{{ route('home') }}">
                <label class="makazi-search-location" for="home-room-type">
                    <span class="makazi-search-label"><i class="bi bi-house"></i> AINA YA MAKAZI</span>
                    <select id="home-room-type" name="room_type">
                        <option value="">Aina zote</option>
                        @foreach ($roomTypes as $roomType)
                            <option value="{{ $roomType->slug }}" @selected(request('room_type') === $roomType->slug)>{{ $roomType->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="makazi-search-location" for="home-location">
                    <span class="makazi-search-label"><i class="bi bi-geo-alt"></i> ENEO</span>
                    <select id="home-location" name="location">
                        <option value="">Tanzania yote</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->name }}" @selected(request('location') === $region->name)>{{ $region->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit"><i class="bi bi-search"></i><span>Tafuta</span></button>
            </form>

            <div class="makazi-stats">
                <div class="makazi-stat">
                    <span class="makazi-stat-icon"><i class="bi bi-house-door"></i></span>
                    <span><strong>{{ number_format($listingCount) }}</strong><small>Nyumba zilizochapishwa</small></span>
                </div>
                <span class="makazi-stat-divider" aria-hidden="true"></span>
                <div class="makazi-stat">
                    <span class="makazi-stat-icon"><i class="bi bi-people"></i></span>
                    <span><strong>{{ number_format($ownerCount) }}</strong><small>Wamiliki wenye matangazo</small></span>
                </div>
            </div>
        </div>
        <span class="makazi-hero-note">MAKAZI BORA, MAISHA BORA</span>
    </section>

    <div class="makazi-home-content">
        <nav class="makazi-category-strip" aria-label="Aina za makazi">
            <a href="{{ route('home') }}" class="makazi-category-chip">
                <i class="bi bi-grid"></i> Nyumba zote
            </a>
            @foreach ($roomTypes as $roomType)
                <a href="{{ route('home', ['room_type' => $roomType->slug]) }}" class="makazi-category-chip">
                    <i class="bi bi-house-door"></i> {{ $roomType->name }}
                </a>
            @endforeach
        </nav>

        @if ($hasSearchFilters)
            <section class="makazi-section">
                <div class="makazi-section-heading">
                    <div>
                        <span class="makazi-section-kicker">MATOKEO YA UTAFUTAJI</span>
                        <h2>Matangazo yanayolingana</h2>
                        <p>{{ number_format($searchResults->total()) }} matangazo yamepatikana</p>
                    </div>
                    <a href="{{ route('home') }}" class="makazi-text-link">Futa vichujio <i class="bi bi-x-circle"></i></a>
                </div>
                <form class="aura-home-filter-form" method="GET" action="{{ route('home') }}">
                    <div>
                        <label for="filter-location">Eneo</label>
                        <input id="filter-location" name="location" class="form-control" value="{{ request('location') }}" placeholder="Mkoa, wilaya au alama">
                    </div>
                    <div>
                        <label for="filter-property-type">Aina ya makazi</label>
                        <select id="filter-property-type" name="property_type" class="form-control">
                            <option value="">Aina zote</option>
                            @foreach (['room' => 'Chumba', 'house' => 'Nyumba', 'residential_plot' => 'Kiwanja cha makazi', 'commercial_plot' => 'Kiwanja cha biashara', 'farm' => 'Shamba', 'commercial_space' => 'Eneo la biashara', 'office' => 'Ofisi'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('property_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-transaction-type">Muamala</label>
                        <select id="filter-transaction-type" name="transaction_type" class="form-control">
                            <option value="">Kupanga au kununua</option>
                            <option value="rent" @selected(request('transaction_type') === 'rent')>Kupanga</option>
                            <option value="sale" @selected(request('transaction_type') === 'sale')>Kununua</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-room-type">Aina ya chumba</label>
                        <select id="filter-room-type" name="room_type" class="form-control">
                            <option value="">Aina zote</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType->slug }}" @selected(request('room_type') === $roomType->slug)>{{ $roomType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-min-price">Bei ya chini (TZS)</label>
                        <input id="filter-min-price" name="min_price" type="number" min="0" class="form-control" value="{{ request('min_price') }}">
                    </div>
                    <div>
                        <label for="filter-max-price">Bei ya juu (TZS)</label>
                        <input id="filter-max-price" name="max_price" type="number" min="0" class="form-control" value="{{ request('max_price') }}">
                    </div>
                    <label class="aura-home-installment-filter">
                        <input type="checkbox" name="installment" value="1" @checked(request()->boolean('installment'))>
                        Malipo kwa awamu yanaruhusiwa
                    </label>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Tafuta</button>
                </form>

                @if ($searchResults->isEmpty())
                    <div class="makazi-empty-note">Hakuna matangazo yaliyopatikana. Badilisha vichujio ujaribu tena.</div>
                @else
                    @include('home.partials.listing-cards', ['listings' => $searchResults])
                    @if ($searchResults->hasPages())
                        <div class="d-flex justify-content-center mt-4">{{ $searchResults->links() }}</div>
                    @endif
                @endif
            </section>
        @else
        <section class="makazi-section">
            <div class="makazi-section-heading">
                <div>
                    <span class="makazi-section-kicker">ZILIZOCHAGULIWA</span>
                    <h2>Makazi yaliyotangazwa</h2>
                    <p>Angalia nyumba na vyumba vinavyopatikana sasa hivi.</p>
                </div>
                <a href="{{ route('home') }}" class="makazi-text-link">Tazama nyumba zote <i class="bi bi-arrow-right"></i></a>
            </div>
            @if ($featuredListings->isNotEmpty())
                @include('home.partials.listing-cards', ['listings' => $featuredListings])
            @else
                <div class="makazi-empty-state">
                    <span><i class="bi bi-house"></i></span>
                    <div><strong>Matangazo mapya yanakuja hivi karibuni</strong><p>Hakuna nyumba zilizochapishwa bado. Tembelea tena au tangaza nyumba yako.</p></div>
                    <a href="@auth{{ auth()->user()->isOwner() || auth()->user()->isAdmin() ? route('owner.listings.create') : route('register') }}@else{{ route('register') }}@endauth">Tangaza nyumba</a>
                </div>
            @endif
        </section>

        <section class="makazi-section makazi-popular-searches">
            <div class="makazi-section-heading">
                <div>
                    <span class="makazi-section-kicker">CHAGUA KWA URAHISI</span>
                    <h2>Tafuta kwa aina ya nyumba</h2>
                    <p>Anza na aina ya chumba unachohitaji.</p>
                </div>
            </div>
            <div class="makazi-search-shortcuts">
                @forelse ($roomTypes->take(6) as $roomType)
                    <a href="{{ route('home', ['room_type' => $roomType->slug]) }}" class="makazi-shortcut-card">
                        <span class="makazi-shortcut-icon"><i class="bi bi-house-door"></i></span>
                        <strong>{{ $roomType->name }}</strong>
                        <small>Tazama matangazo <i class="bi bi-arrow-up-right"></i></small>
                    </a>
                @empty
                    <p class="text-muted">Aina za nyumba zitaonekana hapa hivi karibuni.</p>
                @endforelse
            </div>
        </section>

        <section class="makazi-section makazi-recent-section">
            <div class="makazi-section-heading">
                <div>
                    <span class="makazi-section-kicker">MATANGAZO MAPYA</span>
                    <h2>Zilizoongezwa hivi karibuni</h2>
                    <p>Gundua matangazo mapya kutoka kwa wamiliki.</p>
                </div>
                <a href="{{ route('home') }}" class="makazi-text-link">Tazama yote <i class="bi bi-arrow-right"></i></a>
            </div>
            @if ($recentListings->isNotEmpty())
                @include('home.partials.listing-cards', ['listings' => $recentListings])
            @else
                <div class="makazi-empty-note">Matangazo mapya yataonekana hapa yatakapochapishwa.</div>
            @endif
        </section>

        <section class="makazi-section">
            <div class="makazi-section-heading">
                <div>
                    <span class="makazi-section-kicker">GUNDUA TANZANIA</span>
                    <h2>Tafuta nyumba kwa mkoa</h2>
                    <p>Chagua eneo unalopendelea na uone matangazo yake.</p>
                </div>
                <a href="{{ route('home') }}" class="makazi-text-link">Mikoa yote <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="makazi-region-grid">
                @foreach ($popularRegions as $region)
                    <a class="makazi-region-card" href="{{ route('home', ['location' => $region->name]) }}">
                        <span class="makazi-region-icon"><i class="bi bi-geo-alt"></i></span>
                        <span><strong>{{ $region->name }}</strong><small>{{ number_format($region->rooms_count) }} {{ \Illuminate\Support\Str::plural('tangazo', $region->rooms_count) }}</small></span>
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="makazi-owner-band">
            <div class="makazi-owner-copy">
                <span class="makazi-section-kicker">WAMILIKI NA MADALALI</span>
                <h2>Weka nyumba yako ionekane.</h2>
                <p>Tangaza nyumba yako kwenye Makazi na ufikie wapangaji wanaotafuta makazi.</p>
                <a href="@auth{{ auth()->user()->isOwner() || auth()->user()->isAdmin() ? route('owner.listings.create') : route('register') }}@else{{ route('register') }}@endauth" class="makazi-owner-button">Tangaza nyumba <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="makazi-owner-art" aria-hidden="true"><i class="bi bi-buildings"></i></div>
            <div class="makazi-owner-list">
                <h3>Wamiliki wenye matangazo</h3>
                @forelse ($activeOwners as $owner)
                    <div class="makazi-owner-row">
                        <span class="makazi-owner-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($owner->name, 0, 1)) }}</span>
                        <span><strong>{{ $owner->name }}</strong><small>{{ number_format($owner->published_rooms_count) }} matangazo</small></span>
                    </div>
                @empty
                    <p class="makazi-owner-empty">Wamiliki wataonekana hapa baada ya kuchapisha matangazo yao.</p>
                @endforelse
            </div>
        </section>

        <section class="makazi-section">
            <div class="makazi-section-heading">
                <div>
                    <span class="makazi-section-kicker">KODI YA NYUMBA</span>
                    <h2>Bei za kupanga kwa mkoa</h2>
                    <p>Makadirio yanayotokana na matangazo yaliyochapishwa kwenye Makazi.</p>
                </div>
            </div>
            @if ($rentGuides->isNotEmpty())
                <div class="makazi-rent-table-wrap">
                    <table class="makazi-rent-table">
                        <thead>
                            <tr><th>Mkoa</th><th>Bei ya chini</th><th>Bei ya juu</th><th>Wastani</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($rentGuides as $guide)
                                <tr>
                                    <td><strong>{{ $guide->region_name }}</strong><small>{{ number_format($guide->listing_count) }} matangazo</small></td>
                                    <td>TZS {{ number_format((float) $guide->min_rent, 0) }}</td>
                                    <td>TZS {{ number_format((float) $guide->max_rent, 0) }}</td>
                                    <td>TZS {{ number_format((float) $guide->average_rent, 0) }}</td>
                                    <td><a href="{{ route('home', ['location' => $guide->region_name]) }}">Tafuta <i class="bi bi-arrow-right"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="makazi-empty-note">Taarifa za bei zitaonekana baada ya nyumba kuchapishwa.</div>
            @endif
        </section>

        <section class="makazi-how-section">
            <div class="makazi-section-heading makazi-centered-heading">
                <div>
                    <span class="makazi-section-kicker">RAHISI NA WAZI</span>
                    <h2>Jinsi inavyofanya kazi</h2>
                    <p>Pata makazi yako kwa hatua nne rahisi.</p>
                </div>
            </div>
            <div class="makazi-how-grid">
                @foreach ([
                    ['search', 'Tafuta makazi', 'Tumia eneo na aina ya chumba kuchuja matangazo yanayokufaa.'],
                    ['telephone', 'Wasiliana na mmiliki', 'Tumia maelezo ya mawasiliano kwenye tangazo kuwasiliana moja kwa moja.'],
                    ['geo', 'Tembelea nyumba', 'Panga kutembelea makazi na kuthibitisha maelezo kabla ya kulipia.'],
                    ['check2-circle', 'Kubali makubaliano', 'Jadiliana na mmiliki na kubaliana masharti ya upangaji.'],
                ] as $index => [$icon, $title, $description])
                    <article class="makazi-how-card">
                        <span class="makazi-how-number">0{{ $index + 1 }}</span>
                        <span class="makazi-how-icon"><i class="bi bi-{{ $icon }}"></i></span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="makazi-section makazi-faq-section">
            <div class="makazi-section-heading makazi-centered-heading">
                <div>
                    <span class="makazi-section-kicker">MAJIBU YA HARAKA</span>
                    <h2>Maswali yanayoulizwa mara kwa mara</h2>
                    <p>Unachohitaji kujua kuhusu kutafuta au kutangaza nyumba.</p>
                </div>
            </div>
            <div class="makazi-faq-list">
                <details>
                    <summary>Ninawezaje kutafuta nyumba au chumba?</summary>
                    <p>Chagua aina ya chumba na mkoa unaotaka kwenye kisanduku cha utafutaji, kisha bonyeza Tafuta. Unaweza pia kuchuja kwa kiwango cha kodi kwenye ukurasa wa matangazo.</p>
                </details>
                <details>
                    <summary>Je, ninaweza kutafuta nyumba bila akaunti?</summary>
                    <p>Ndio. Unaweza kuvinjari matangazo bila kuingia. Akaunti inahitajika kwa baadhi ya huduma za wamiliki.</p>
                </details>
                <details>
                    <summary>Ninawezaje kutangaza nyumba yangu?</summary>
                    <p>Fungua akaunti ya mmiliki, ongeza maelezo ya nyumba na picha, kisha wasilisha tangazo lako ili likaguliwe.</p>
                </details>
                <details>
                    <summary>Nifanye nini kabla ya kulipia nyumba?</summary>
                    <p>Wasiliana na mmiliki, tembelea nyumba, hakikisha maelezo na kubaliana masharti kabla ya kufanya malipo.</p>
                </details>
            </div>
        </section>
        @endif
    </div>
</x-app-layout>
