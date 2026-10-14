<x-guest-layout>
    <x-slot name="title">Create your account</x-slot>

    <div class="aura-auth-heading aura-auth-heading--compact">
        <span class="aura-auth-form-kicker">JOIN THE COMMUNITY</span>
        <h2>Make yourself at home.</h2>
        <p>Create an owner account and start sharing your property.</p>
    </div>

    @if ($errors->any())
        <div class="aura-auth-alert aura-auth-alert--error" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="aura-auth-form">
        @csrf

        <div class="aura-auth-field-grid">
            <div class="aura-auth-field">
                <label for="name">Full name</label>
                <div class="aura-auth-input-wrap">
                    <i class="bi bi-person" aria-hidden="true"></i>
                    <input id="name" type="text" class="@error('name') is-invalid @enderror"
                        name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Your name">
                </div>
                @error('name')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
            </div>

            <div class="aura-auth-field">
                <label for="username">Username</label>
                <div class="aura-auth-input-wrap">
                    <i class="bi bi-at" aria-hidden="true"></i>
                    <input id="username" type="text" class="@error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="Choose a username">
                </div>
                @error('username')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="aura-auth-field">
            <label for="email">Email address</label>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input id="email" type="email" class="@error('email') is-invalid @enderror"
                    name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
            </div>
            @error('email')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="aura-auth-field">
            <label for="whatsapp_number">WhatsApp number</label>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                <input id="whatsapp_number" type="tel" class="@error('whatsapp_number') is-invalid @enderror"
                    name="whatsapp_number" value="{{ old('whatsapp_number') }}" required autocomplete="tel"
                    inputmode="tel" placeholder="+255 712 345 678">
            </div>
            <small class="aura-auth-role-note">Include your country code. You can use this number with your password to log in.</small>
            @error('whatsapp_number')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="aura-auth-field-grid">
            <div class="aura-auth-field">
                <label for="password">Password</label>
                <div class="aura-auth-input-wrap">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    <input id="password" type="password" class="@error('password') is-invalid @enderror"
                        name="password" required autocomplete="new-password" placeholder="Create password">
                    <button class="aura-auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
            </div>

            <div class="aura-auth-field">
                <label for="password_confirmation">Confirm password</label>
                <div class="aura-auth-input-wrap">
                    <i class="bi bi-lock-fill" aria-hidden="true"></i>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                        required autocomplete="new-password" placeholder="Repeat password">
                    <button class="aura-auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="aura-auth-role-note">
            <i class="bi bi-house-heart" aria-hidden="true"></i>
            New registrations are owner accounts. Administrator accounts are created by system administrators.
        </div>

        <button type="submit" class="aura-auth-submit">
            <span>Create my account</span><i class="bi bi-arrow-right" aria-hidden="true"></i>
        </button>
    </form>

    <div class="aura-auth-switch">
        <span>Already have an account?</span>
        <a href="{{ route('login') }}">Log in <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>

    <div class="aura-auth-trust"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Your information is protected and secure</div>
</x-guest-layout>
