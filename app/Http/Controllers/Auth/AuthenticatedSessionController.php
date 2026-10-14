<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\WhatsAppOtpDeliveryException;
use App\Services\WhatsAppOtpSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, WhatsAppOtpSender $whatsappOtpSender): RedirectResponse
    {
        if ($request->isWhatsAppLogin()) {
            $user = $request->authenticateWhatsappCredentials();
            $otp = (string) random_int(100000, 999999);

            try {
                $whatsappOtpSender->send($request->validated('whatsapp_number'), $otp);
            } catch (WhatsAppOtpDeliveryException $exception) {
                throw ValidationException::withMessages([
                    'whatsapp_number' => $exception->getMessage(),
                ]);
            }

            $challenge = [
                'user_id' => $user?->id,
                'whatsapp_number' => $request->validated('whatsapp_number'),
                'code_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5)->timestamp,
                'attempts' => 0,
                'remember' => $request->boolean('remember'),
                'is_new_account' => $user === null,
            ];

            if (! $user) {
                $challenge['password_hash'] = Hash::make($request->string('password')->toString());
            }

            $request->session()->put('auth.whatsapp_challenge', $challenge);

            return redirect()->route('whatsapp.otp');
        }

        $request->authenticate();

        $request->session()->regenerate();

        return $this->redirectAuthenticatedUser($request->user());
    }

    public function showWhatsappOtp(Request $request): View|RedirectResponse
    {
        $challenge = $request->session()->get('auth.whatsapp_challenge');
        if (! is_array($challenge)) {
            return redirect()->route('login');
        }

        return view('auth.verify-whatsapp-otp', [
            'phoneSuffix' => substr($challenge['whatsapp_number'], -4),
            'isNewAccount' => $challenge['is_new_account'] ?? false,
        ]);
    }

    public function verifyWhatsappOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);
        $challenge = $request->session()->get('auth.whatsapp_challenge');

        if (! is_array($challenge) || $challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('auth.whatsapp_challenge');

            return redirect()->route('login')->withErrors([
                'whatsapp_number' => 'This verification code has expired. Please log in again to request a new one.',
            ]);
        }

        $user = $challenge['user_id'] ? User::find($challenge['user_id']) : null;
        if ($challenge['user_id'] && ! $user) {
            $request->session()->forget('auth.whatsapp_challenge');

            return redirect()->route('login')->withErrors([
                'whatsapp_number' => 'We could not verify this login. Please try again.',
            ]);
        }

        if (! Hash::check($validated['otp'], $challenge['code_hash'])) {
            $challenge['attempts']++;
            if ($challenge['attempts'] >= 5) {
                $request->session()->forget('auth.whatsapp_challenge');

                return redirect()->route('login')->withErrors([
                    'whatsapp_number' => 'Too many incorrect codes. Please log in again to request a new one.',
                ]);
            }

            $request->session()->put('auth.whatsapp_challenge', $challenge);

            throw ValidationException::withMessages([
                'otp' => 'That verification code is not correct.',
            ]);
        }

        if ($challenge['is_new_account'] ?? false) {
            $user = User::where('whatsapp_number', $challenge['whatsapp_number'])->first();

            if ($user) {
                $request->session()->forget('auth.whatsapp_challenge');

                return redirect()->route('login')->withErrors([
                    'whatsapp_number' => 'This WhatsApp number was registered while you were verifying. Please start again and log in with its existing password.',
                ]);
            }

            Role::firstOrCreate(
                ['name' => 'owner'],
                ['description' => 'Property owner who can list and manage rooms'],
            );
            $user = User::create([
                'name' => 'WhatsApp user',
                'email' => 'whatsapp.'.$challenge['whatsapp_number'].'@accounts.aura.invalid',
                'whatsapp_number' => $challenge['whatsapp_number'],
                'password' => $challenge['password_hash'],
            ]);
            $user->assignRole('owner');
        }

        if (! $user->canLogin()) {
            $request->session()->forget('auth.whatsapp_challenge');

            return redirect()->route('login')->withErrors([
                'whatsapp_number' => trans('auth.failed'),
            ]);
        }

        $request->session()->forget(['auth.whatsapp_challenge', 'auth.whatsapp_otp_preview']);
        Auth::login($user, (bool) $challenge['remember']);
        $request->session()->regenerate();

        return $this->redirectAuthenticatedUser($user);
    }

    private function redirectAuthenticatedUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($user->isOwner()) {
            return redirect()->intended(route('owner.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
