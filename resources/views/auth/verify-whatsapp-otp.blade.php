<x-guest-layout>
    <x-slot name="title">Verify WhatsApp login</x-slot>

    <div class="aura-auth-heading">
        <span class="aura-auth-form-kicker">ONE MORE STEP</span>
        @if ($isNewAccount)
            <h2>Verify your number.</h2>
            <p>Enter the six-digit code sent to your WhatsApp number to finish creating your account.</p>
        @else
            <h2>Verify your number.</h2>
            <p>Enter the six-digit code sent to your WhatsApp number to finish logging in.</p>
        @endif
    </div>

    @if ($errors->any())
        <div class="aura-auth-alert aura-auth-alert--error" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="aura-auth-alert aura-auth-alert--success" role="status">
        <i class="bi bi-whatsapp" aria-hidden="true"></i>
        <span>We sent a 6-digit verification code to the WhatsApp number ending in <strong>{{ $phoneSuffix }}</strong>.</span>
    </div>

    <form method="POST" action="{{ route('whatsapp.otp.verify') }}" class="aura-auth-form">
        @csrf

        <div class="aura-auth-field">
            <label for="otp">6-digit verification code</label>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <input id="otp" type="text" name="otp" inputmode="numeric" autocomplete="one-time-code"
                    pattern="[0-9]{6}" maxlength="6" minlength="6" required autofocus
                    class="@error('otp') is-invalid @enderror" placeholder="Enter your 6-digit code">
            </div>
            <small class="aura-login-hint">The code expires in 5 minutes. Check WhatsApp on the number shown above.</small>
        </div>

        <button type="submit" class="aura-auth-submit">
            <span>Verify and log in</span><i class="bi bi-arrow-right" aria-hidden="true"></i>
        </button>
    </form>

    <div class="aura-auth-switch">
        <span>Using a different account?</span>
        <a href="{{ route('login') }}">Return to login <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>

    <div class="aura-auth-trust"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Your information is protected and secure</div>
</x-guest-layout>
