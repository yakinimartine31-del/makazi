@php
$currentRoute = request()->route() ? request()->route()->getName() : '';
$isOwner = auth()->check() && auth()->user()->isOwner();
$isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}" aria-label="AURA - Affordable Urban Relaxing Accommodations">
            <img src="{{ asset('storage/room-images/logo.jpeg') }}" alt="AURA - Affordable Urban Relaxing Accommodations" class="aura-logo aura-logo--navbar">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Find Rooms</a>
                </li>
                @auth
                    @if($isAdmin)
                        <li class="nav-item"><a class="nav-link {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                    @elseif($isOwner)
                        <li class="nav-item"><a class="nav-link {{ $currentRoute === 'owner.dashboard' ? 'active' : '' }}" href="{{ route('owner.dashboard') }}">Owner Dashboard</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-expanded="false">
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
