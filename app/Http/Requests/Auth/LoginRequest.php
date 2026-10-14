<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $login = trim((string) $this->input('email', ''));
        $digits = preg_replace('/\D+/', '', $login);
        $isPhoneNumber = preg_match('/^[+\d\s().-]+$/', $login)
            && strlen($digits) >= 8
            && strlen($digits) <= 15;
        $method = $this->input('login_method');

        if (! in_array($method, ['email', 'whatsapp'], true)) {
            $method = $isPhoneNumber ? 'whatsapp' : 'email';
        }

        $values = ['login_method' => $method];

        if ($method === 'whatsapp') {
            $values['whatsapp_number'] = preg_replace(
                '/\D+/',
                '',
                (string) $this->input('whatsapp_number', $login),
            );
        }

        $this->merge($values);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login_method' => ['required', 'in:email,whatsapp'],
            'email' => ['required_if:login_method,email', 'string'],
            'whatsapp_number' => ['required_if:login_method,whatsapp', 'string', 'regex:/^[1-9]\d{7,14}$/'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $isWhatsAppLogin = $this->input('login_method') === 'whatsapp';
        $login = trim((string) $this->input($isWhatsAppLogin ? 'whatsapp_number' : 'email'));
        $loginField = $isWhatsAppLogin
            ? 'whatsapp_number'
            : (filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username');
        $loginValue = $isWhatsAppLogin ? $login : Str::lower($login);

        $authenticated = Auth::attempt([
            $loginField => $loginValue,
            'password' => $this->string('password')->toString(),
        ], $this->boolean('remember'));

        if (! $authenticated || ! $this->user()?->canLogin()) {
            if ($authenticated) {
                Auth::logout();
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $isWhatsAppLogin ? 'whatsapp_number' : 'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function isWhatsAppLogin(): bool
    {
        return $this->input('login_method') === 'whatsapp';
    }

    /**
     * Check WhatsApp login credentials without authenticating before OTP verification.
     *
     * @throws ValidationException
     */
    public function authenticateWhatsappCredentials(): ?User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('whatsapp_number', $this->validated('whatsapp_number'))->first();

        if ($user && (
            ! Hash::check($this->string('password')->toString(), $user->password)
            || ! $user->canLogin()
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'whatsapp_number' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            $this->input('login_method') === 'whatsapp' ? 'whatsapp_number' : 'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->input('login_method') === 'whatsapp'
            ? $this->input('whatsapp_number')
            : $this->input('email');

        return Str::transliterate(Str::lower((string) $identifier).'|'.$this->ip());
    }
}
