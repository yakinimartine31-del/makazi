<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AURA') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/admin-lte/css/adminlte.min.css') }}">
</head>
<body class="bg-light min-vh-100 d-flex flex-column">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 font-weight-bold mb-4">Welcome to AURA</h1>
                <p class="lead text-muted mb-5">
                    AURA connects property owners with tenants across Tanzania.
                    Find your perfect accommodation easily and securely.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 mb-2 mb-sm-0 mr-sm-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</body>
</html>
