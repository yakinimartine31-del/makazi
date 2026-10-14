@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AURA') }}{{ $title ? ' - '.$title : 'Authentication' }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="aura-auth-body">
    <main class="aura-auth-shell">
        <a href="{{ route('home') }}" class="aura-auth-home-link">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to AURA
        </a>

        <div class="aura-auth-layout">
            <aside class="aura-auth-story">
                <div class="aura-auth-story-glow aura-auth-story-glow--one"></div>
                <div class="aura-auth-story-glow aura-auth-story-glow--two"></div>
                <a href="{{ route('home') }}" class="aura-auth-brand" aria-label="AURA home">
                    <img src="{{ asset('storage/room-images/logo.jpeg') }}" alt="AURA logo">
                    <span><strong>AURA</strong><small>Affordable Urban Relaxing Accommodations</small></span>
                </a>

                <div class="aura-auth-story-copy">
                    <span class="aura-auth-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> YOUR NEXT CHAPTER STARTS HERE</span>
                    <h1>Find a place<br>to <em>feel at home.</em></h1>
                    <p>Discover welcoming rooms and homes across Tanzania, or share a space of your own with people looking for their next home.</p>
                </div>

                <div class="aura-auth-illustration" aria-hidden="true">
                    <div class="aura-auth-sun"></div>
                    <div class="aura-auth-hill aura-auth-hill--back"></div>
                    <div class="aura-auth-hill aura-auth-hill--front"></div>
                    <div class="aura-auth-house">
                        <div class="aura-auth-roof"></div>
                        <div class="aura-auth-house-body">
                            <span class="aura-auth-window aura-auth-window--left"></span>
                            <span class="aura-auth-door"></span>
                            <span class="aura-auth-window aura-auth-window--right"></span>
                        </div>
                    </div>
                    <span class="aura-auth-floating-card aura-auth-floating-card--top"><i class="bi bi-geo-alt-fill"></i> Somewhere just right</span>
                    <span class="aura-auth-floating-card aura-auth-floating-card--bottom"><i class="bi bi-heart-fill"></i> A place to belong</span>
                </div>

                <div class="aura-auth-benefits">
                    <span><i class="bi bi-check-circle-fill"></i> Browse local listings</span>
                    <span><i class="bi bi-check-circle-fill"></i> Connect with property owners</span>
                    <span><i class="bi bi-check-circle-fill"></i> Make your next move with confidence</span>
                </div>

                <div class="aura-auth-story-footer">
                    <span>Made for finding your place in Tanzania</span>
                    <span class="aura-auth-footer-mark"><i class="bi bi-house-heart-fill"></i> AURA</span>
                </div>
            </aside>

            <section class="aura-auth-panel">
                <div class="aura-auth-form-wrap">
                    {{ $slot }}
                </div>
                <p class="aura-auth-copyright">&copy; {{ date('Y') }} AURA. Affordable Urban Relaxing Accommodations.</p>
            </section>
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) return;

                const showing = input.type === 'password';
                input.type = showing ? 'text' : 'password';
                button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', String(showing));
                button.querySelector('i').className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
</body>
</html>
