<x-guest-layout>
    <x-slot name="title">Log in</x-slot>

    <div class="aura-auth-heading">
        <span class="aura-auth-form-kicker">WELCOME BACK</span>
        <h2>Good to see you.</h2>
        <p>Log in to continue your AURA journey.</p>
    </div>

    @if (session('status'))
        <div class="aura-auth-alert aura-auth-alert--success" role="status">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="aura-auth-alert aura-auth-alert--error" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="aura-auth-form">
        @csrf

        <fieldset class="aura-login-method">
            <legend>Choose how to log in</legend>
            <div class="aura-login-method-options">
                <label class="aura-login-method-option">
                    <input type="radio" name="login_method" value="email"
                        @checked(old('login_method', 'email') === 'email')>
                    <span><i class="bi bi-envelope" aria-hidden="true"></i> Email or username</span>
                </label>
                <label class="aura-login-method-option">
                    <input type="radio" name="login_method" value="whatsapp"
                        @checked(old('login_method') === 'whatsapp')>
                    <span><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp number</span>
                </label>
            </div>
        </fieldset>

        <div class="aura-auth-field" data-login-identifier="email">
            <label for="email">Email or username</label>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-person" aria-hidden="true"></i>
                <input id="email" type="text" class="@error('email') is-invalid @enderror"
                    name="email" value="{{ old('email') }}" autocomplete="username"
                    autofocus placeholder="Enter your email or username">
            </div>
            @error('email')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="aura-auth-field" data-login-identifier="whatsapp" hidden>
            <label for="whatsapp_number">WhatsApp number</label>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                <input id="whatsapp_number" type="tel" class="@error('whatsapp_number') is-invalid @enderror"
                    name="whatsapp_number" value="{{ old('whatsapp_number') }}" autocomplete="tel"
                    inputmode="tel" placeholder="+255 712 345 678">
            </div>
            <small class="aura-login-hint">Enter the country code you registered with, then your password.</small>
            <small class="aura-login-hint">First time here? A new WhatsApp number creates a tenant account after verification.</small>
            @error('whatsapp_number')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="aura-auth-field">
            <div class="aura-auth-label-row">
                <label for="password">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="aura-auth-input-wrap">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input id="password" type="password" class="@error('password') is-invalid @enderror"
                    name="password" required autocomplete="current-password" placeholder="Enter your password">
                <button class="aura-auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')<span class="aura-auth-field-error">{{ $message }}</span>@enderror
        </div>

        <label class="aura-auth-remember" for="remember_me">
            <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
            <span class="aura-auth-checkmark"><i class="bi bi-check" aria-hidden="true"></i></span>
            <span>Keep me signed in</span>
        </label>

        <button type="submit" class="aura-auth-submit">
            <span>Log in to AURA</span><i class="bi bi-arrow-right" aria-hidden="true"></i>
        </button>
    </form>

    <div class="aura-auth-switch">
        <span>New to AURA?</span>
        <a href="{{ route('register') }}">Create your account <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>

    <div class="aura-auth-trust"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Your information is protected and secure</div>

    <script>
        (() => {
            const methodInputs = document.querySelectorAll('input[name="login_method"]');
            const identifiers = document.querySelectorAll('[data-login-identifier]');

            const updateLoginMethod = () => {
                const selectedMethod = document.querySelector('input[name="login_method"]:checked')?.value || 'email';

                identifiers.forEach((field) => {
                    const isSelected = field.dataset.loginIdentifier === selectedMethod;
                    field.hidden = !isSelected;
                    const input = field.querySelector('input');
                    input.disabled = !isSelected;
                    input.required = isSelected;
                    if (isSelected) input.focus({ preventScroll: true });
                });
            };

            methodInputs.forEach((input) => input.addEventListener('change', updateLoginMethod));
            updateLoginMethod();
        })();
    </script>
</x-guest-layout>
