<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AURA') }} - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/admin-lte/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-dashboard-sidebar-toggle role="button" aria-label="Toggle sidebar" aria-expanded="true">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                @if (auth()->check())
                    <li class="nav-item">
                        <span class="nav-link">
                            {{ auth()->user()->name }}
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-secondary">
                                <i class="bi bi-box-arrow-right mr-1"></i> Logout
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link {{ request()->is('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('register') ? 'active' : '' }}" href="{{ route('register') }}">Register</a></li>
                @endif
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ asset('storage/room-images/logo.jpeg') }}" alt="AURA - Affordable Urban Relaxing Accommodations" class="aura-logo aura-logo--dashboard">
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        @php $user = auth()->user(); @endphp

                        @if ($user && $user->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-speedometer2"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.listings.index') }}" class="nav-link {{ request()->is('admin/listings*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-house-door"></i>
                                    <p>Listings</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-people"></i>
                                    <p>Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-credit-card"></i>
                                    <p>Payments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-bar-chart"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                        @elseif ($user && $user->isOwner())
                            <li class="nav-item">
                                <a href="{{ route('owner.dashboard') }}" class="nav-link {{ request()->is('owner/dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-speedometer2"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.listings.index') }}" class="nav-link {{ request()->is('owner/listings*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-house-door"></i>
                                    <p>My Listings</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.listings.create') }}" class="nav-link {{ request()->is('owner/listings/create') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-plus-lg"></i>
                                    <p>Create Listing</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.payments.index') }}" class="nav-link {{ request()->is('owner/payments*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-credit-card"></i>
                                    <p>Payments</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="content-wrapper">
            @if (isset($pageTitle))
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">{{ $pageTitle }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </main>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">Laravel 12 + AdminLTE 3</div>
            <strong>&copy; {{ date('Y') }} AURA.</strong> All rights reserved.
        </footer>
    </div>

    <script>
        document.querySelectorAll('[data-dashboard-sidebar-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();

                if (window.matchMedia('(max-width: 767.98px)').matches) {
                    document.body.classList.toggle('sidebar-open');
                    toggle.setAttribute(
                        'aria-expanded',
                        document.body.classList.contains('sidebar-open') ? 'true' : 'false'
                    );
                    return;
                }

                document.body.classList.toggle('sidebar-collapse');
                toggle.setAttribute(
                    'aria-expanded',
                    document.body.classList.contains('sidebar-collapse') ? 'false' : 'true'
                );
            });
        });
    </script>
</body>
</html>
