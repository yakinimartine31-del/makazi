<x-guest-layout>
    <x-slot name="title">
        Verify Email
    </x-slot>

    <div class="mb-4">
        <p class="text-muted mb-0">
            Please verify your email address by clicking the button below.
            If you didn't receive the email, we can send another.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Resend Verification Email
            </button>
        </div>
    </form>
</x-guest-layout>
