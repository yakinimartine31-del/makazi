@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AURA') }}{{ $title ? ' - ' . $title : '' }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/admin-lte/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/property-detail.css') }}">
</head>
<body class="{{ request()->routeIs('home') ? 'makazi-home' : '' }}">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary {{ request()->routeIs('home') ? 'makazi-home-nav' : '' }}">
        <div class="container">
            <a class="navbar-brand aura-brand" href="{{ route('home') }}" aria-label="AURA - Affordable Urban Relaxing Accommodations">
                <img src="{{ asset('storage/room-images/logo.jpeg') }}" alt="AURA - Affordable Urban Relaxing Accommodations" class="aura-logo aura-logo--navbar">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                @php
                    $browseMenus = [
                        'Viwanja' => [
                            ['label' => 'Viwanja vya makazi', 'filters' => ['property_type' => 'residential_plot', 'transaction_type' => 'sale']],
                            ['label' => 'Viwanja vya biashara', 'filters' => ['property_type' => 'commercial_plot', 'transaction_type' => 'sale']],
                            ['label' => 'Mashamba yanayouzwa', 'filters' => ['property_type' => 'farm', 'transaction_type' => 'sale']],
                            ['label' => 'Mashamba ya kupanga', 'filters' => ['property_type' => 'farm', 'transaction_type' => 'rent']],
                            ['label' => 'Viwanja kwa awamu', 'filters' => ['property_type' => 'residential_plot', 'transaction_type' => 'sale', 'installment' => 1]],
                        ],
                        'Nyumba' => [
                            ['label' => 'Nyumba za kupanga', 'filters' => ['property_type' => 'house', 'transaction_type' => 'rent']],
                            ['label' => 'Nyumba zinazouzwa', 'filters' => ['property_type' => 'house', 'transaction_type' => 'sale']],
                            ['label' => 'Sehemu za biashara', 'filters' => ['property_type' => 'commercial_space', 'transaction_type' => 'rent']],
                            ['label' => 'Ofisi za kupanga', 'filters' => ['property_type' => 'office', 'transaction_type' => 'rent']],
                        ],
                        'Nunua' => [
                            ['label' => 'Nunua kiwanja', 'filters' => ['property_type' => 'residential_plot', 'transaction_type' => 'sale']],
                            ['label' => 'Nunua shamba', 'filters' => ['property_type' => 'farm', 'transaction_type' => 'sale']],
                            ['label' => 'Nunua nyumba', 'filters' => ['property_type' => 'house', 'transaction_type' => 'sale']],
                        ],
                        'Kupanga' => [
                            ['label' => 'Panga nyumba', 'filters' => ['property_type' => 'house', 'transaction_type' => 'rent']],
                            ['label' => 'Panga sehemu ya biashara', 'filters' => ['property_type' => 'commercial_space', 'transaction_type' => 'rent']],
                            ['label' => 'Panga ofisi', 'filters' => ['property_type' => 'office', 'transaction_type' => 'rent']],
                        ],
                    ];
                @endphp
                <ul class="navbar-nav mr-auto makazi-browse-nav">
                    <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">Nyumbani</a></li>
                    @foreach ($browseMenus as $menu => $items)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="{{ route('home') }}" id="browse-{{ $loop->index }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ $menu }}</a>
                            <div class="dropdown-menu" aria-labelledby="browse-{{ $loop->index }}">
                                @foreach ($items as $item)
                                    <a class="dropdown-item" href="{{ route('home', $item['filters']) }}">{{ $item['label'] }}</a>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                    <li class="nav-item"><a class="nav-link" href="@auth{{ auth()->user()->isOwner() || auth()->user()->isAdmin() ? route('owner.dashboard') : route('register') }}@else{{ route('register') }}@endauth">Wamiliki &amp; Madalali</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Translate this page">
                            <i class="bi bi-translate" aria-hidden="true"></i><span class="sr-only">Translate this page</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <button class="dropdown-item" type="button" data-translate-language="en">
                                English
                            </button>
                            <button class="dropdown-item" type="button" data-translate-language="sw">
                                Kiswahili
                            </button>
                            <span id="language-translation-status" class="dropdown-item-text small text-muted" role="status" aria-live="polite"></span>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('home') }}" id="listingSearchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Search listings"><i class="bi bi-search"></i></a>
                        <div class="dropdown-menu dropdown-menu-right p-3 makazi-nav-search-menu" aria-labelledby="listingSearchDropdown">
                            <form method="GET" action="{{ route('home') }}">
                                <label class="sr-only" for="nav-listing-search">Search by location</label>
                                <div class="input-group">
                                    <input id="nav-listing-search" type="search" name="location" class="form-control" placeholder="Search by location" required>
                                    <div class="input-group-append"><button class="btn btn-primary" type="submit">Search</button></div>
                                </div>
                            </form>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link makazi-post-link" href="@auth{{ auth()->user()->isOwner() || auth()->user()->isAdmin() ? route('owner.listings.create') : route('register') }}@else{{ route('register') }}@endauth">+ Tangaza</a></li>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                        @elseif (auth()->user()->isOwner())
                            <li class="nav-item"><a class="nav-link" href="{{ route('owner.dashboard') }}">Owner Dashboard</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-light ml-2" href="{{ route('register') }}">Register</a></li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4 {{ request()->routeIs('home') ? 'makazi-home-main' : '' }}">
        {{ $slot }}
    </main>

    <dialog class="aura-property-dialog" id="aura-property-dialog" aria-label="Maelezo ya tangazo">
        <div class="aura-property-dialog-header">
            <span>Maelezo ya tangazo</span>
            <button class="aura-property-dialog-close" type="button" data-property-dialog-close aria-label="Funga maelezo">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="aura-property-dialog-content" id="aura-property-dialog-content" aria-live="polite">
            <p class="aura-property-dialog-message">Inapakia maelezo ya tangazo…</p>
        </div>
    </dialog>

    <footer class="bg-light mt-5 py-4 {{ request()->routeIs('home') ? 'makazi-home-footer' : '' }}">
        @if (request()->routeIs('home'))
            <div class="makazi-footer-content">
                <div class="makazi-footer-brand">
                    <a href="{{ route('home') }}" class="aura-footer-logo" aria-label="AURA home">
                        <img src="{{ asset('storage/room-images/logo.jpeg') }}" alt="AURA - Affordable Urban Relaxing Accommodations" class="aura-logo aura-logo--footer">
                    </a>
                    <p>Njia rahisi ya kupata nyumba na vyumba vya kupanga Tanzania.</p>
                </div>
                <div>
                    <h2>AURA</h2>
                    <a href="{{ route('home') }}">Tafuta nyumba</a>
                    <a href="{{ route('home') }}">Vyumba vya kupanga</a>
                </div>
                <div>
                    <h2>Wamiliki</h2>
                    <a href="@auth{{ auth()->user()->isOwner() || auth()->user()->isAdmin() ? route('owner.dashboard') : route('register') }}@else{{ route('register') }}@endauth">Tangaza nyumba</a>
                    <a href="{{ route('owner.dashboard') }}">Dashibodi ya mmiliki</a>
                </div>
                @guest
                    <div>
                        <h2>Akaunti</h2>
                        <a href="{{ route('login') }}">Ingia</a>
                        <a href="{{ route('register') }}">Jisajili</a>
                    </div>
                @endguest
            </div>
            <div class="makazi-footer-bottom">&copy; {{ date('Y') }} AURA. Haki zote zimehifadhiwa.</div>
        @else
            <div class="container text-center">
                <p class="mb-0 text-muted">&copy; {{ date('Y') }} AURA. All rights reserved.</p>
            </div>
        @endif
    </footer>
    <div id="google_translate_element" class="aura-translate-widget" aria-hidden="true"></div>
    <script>
        (function () {
            var scriptRequested = false;
            var pendingLanguage = null;

            function applyLanguage(language, attempts) {
                var selector = document.querySelector('#google_translate_element select.goog-te-combo');

                if (!selector) {
                    if (attempts > 0) {
                        window.setTimeout(function () {
                            applyLanguage(language, attempts - 1);
                        }, 100);
                    } else {
                        document.getElementById('language-translation-status').textContent = 'The translator did not become ready. Please try again.';
                    }
                    return;
                }

                selector.value = language;
                selector.dispatchEvent(new Event('change'));
                document.getElementById('language-translation-status').textContent = '';
            }

            window.googleTranslateElementInit = function () {
                new google.translate.TranslateElement({
                    pageLanguage: 'sw',
                    includedLanguages: 'en,sw',
                    autoDisplay: false
                }, 'google_translate_element');

                if (pendingLanguage) {
                    applyLanguage(pendingLanguage, 20);
                }
            };

            document.querySelectorAll('[data-translate-language]').forEach(function (button) {
                button.addEventListener('click', function () {
                    pendingLanguage = button.dataset.translateLanguage;
                    document.getElementById('language-translation-status').textContent = 'Loading translator…';

                    if (window.google && window.google.translate) {
                        applyLanguage(pendingLanguage, 20);
                        return;
                    }

                    if (!scriptRequested) {
                        scriptRequested = true;
                        var script = document.createElement('script');
                        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                        script.async = true;
                        script.onerror = function () {
                            document.getElementById('language-translation-status').textContent = 'The translator could not be loaded. Check your connection and try again.';
                        };
                        document.head.appendChild(script);
                    }
                });
            });
        })();
    </script>
    <script>
        document.addEventListener('click', (event) => {
            const trackedElement = event.target.closest('[data-performance-endpoint][data-performance-event]');
            if (trackedElement) {
                fetch(trackedElement.dataset.performanceEndpoint, {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ event: trackedElement.dataset.performanceEvent }),
                }).catch((error) => console.error('Could not record listing activity:', error));
            }

            const likeButton = event.target.closest('[data-performance-like]');
            if (!likeButton || likeButton.disabled) return;

            likeButton.disabled = true;
            fetch(likeButton.dataset.performanceLike, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            })
                .then((response) => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then((result) => {
                    likeButton.classList.toggle('is-liked', result.liked);
                    likeButton.setAttribute('aria-pressed', String(result.liked));
                    likeButton.setAttribute('aria-label', result.liked ? 'Unlike listing' : 'Like listing');
                    likeButton.querySelector('i').className = 'bi bi-heart' + (result.liked ? '-fill' : '');
                    likeButton.querySelector('[data-like-count]').textContent = result.likes.toLocaleString();
                })
                .catch((error) => console.error('Could not update listing like:', error))
                .finally(() => { likeButton.disabled = false; });
        });
    </script>
    <script>
        (() => {
            const dialog = document.getElementById('aura-property-dialog');
            const content = document.getElementById('aura-property-dialog-content');
            if (!dialog || !content) return;

            let requestController = null;

            const renderMessage = (message, link) => {
                content.replaceChildren();
                const paragraph = document.createElement('p');
                paragraph.className = 'aura-property-dialog-message';
                paragraph.textContent = message;
                content.append(paragraph);
                if (link) {
                    const openLink = document.createElement('a');
                    openLink.href = link;
                    openLink.className = 'btn btn-outline-primary';
                    openLink.textContent = 'Fungua ukurasa kamili';
                    content.append(openLink);
                }
            };

            const showListing = async (url) => {
                requestController?.abort();
                requestController = new AbortController();
                renderMessage('Inapakia maelezo ya tangazo…');
                if (!dialog.open) dialog.showModal();

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: requestController.signal,
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);

                    const html = await response.text();
                    const documentFragment = new DOMParser().parseFromString(html, 'text/html');
                    const listing = documentFragment.querySelector('.aura-property-page');
                    if (!listing) throw new Error('Listing details were missing from the response.');

                    content.replaceChildren(listing);
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    console.error('Could not load listing preview:', error);
                    renderMessage('Maelezo ya tangazo hayakuweza kupakiwa. Jaribu tena au fungua ukurasa kamili.', url);
                }
            };

            document.addEventListener('click', (event) => {
                const closeButton = event.target.closest('[data-property-dialog-close]');
                if (closeButton) {
                    dialog.close();
                    return;
                }

                const galleryButton = event.target.closest('[data-gallery-thumbnail], [data-gallery-step]');
                if (galleryButton) {
                    const gallery = galleryButton.closest('.aura-property-gallery');
                    const image = gallery?.querySelector('[data-gallery-current]');
                    const thumbnails = [...(gallery?.querySelectorAll('[data-gallery-thumbnail]') ?? [])];
                    const count = gallery?.querySelector('[data-gallery-count]');
                    if (!image || thumbnails.length === 0) return;

                    let index = Number(galleryButton.dataset.galleryThumbnail);
                    if (galleryButton.hasAttribute('data-gallery-step')) {
                        index = thumbnails.findIndex((thumbnail) => thumbnail.classList.contains('is-active'))
                            + Number(galleryButton.dataset.galleryStep);
                    }
                    index = (index + thumbnails.length) % thumbnails.length;
                    const selected = thumbnails[index];
                    image.src = selected.querySelector('img').src;
                    image.alt = selected.querySelector('img').alt || image.alt;
                    if (count) count.textContent = (index + 1) + ' / ' + thumbnails.length;
                    thumbnails.forEach((thumbnail, itemIndex) => {
                        const active = itemIndex === index;
                        thumbnail.classList.toggle('is-active', active);
                        thumbnail.setAttribute('aria-pressed', String(active));
                    });
                    return;
                }

                const link = event.target.closest('a[href]');
                if (!link || link.target || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                const url = new URL(link.href, window.location.href);
                if (url.origin !== window.location.origin || !/^\/listing\/\d+$/.test(url.pathname)) return;

                event.preventDefault();
                showListing(url.href);
            });

            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
            dialog.addEventListener('close', () => {
                requestController?.abort();
                content.replaceChildren();
            });
        })();
    </script>
</body>
</html>
