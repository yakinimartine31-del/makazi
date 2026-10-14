<x-guest-layout>
    <x-slot name="title">
        Confirm Password
    </x-slot>

    <div class="mb-4">
        <p class="text-muted mb-0">
            Please confirm your password to continue.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="current-password" autofocus>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Confirm
            </button>
        </div>
    </form>
</x-guest-layout>
