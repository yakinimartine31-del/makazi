<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('Choose how to log in')
            ->assertSee('WhatsApp number');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->createUserWithRole('owner');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('owner.dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_their_username(): void
    {
        $user = $this->createUserWithRole('owner', ['username' => 'yakini']);

        $response = $this->post('/login', [
            'email' => 'yakini',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('owner.dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_their_whatsapp_number(): void
    {
        $this->fakeWhatsappDelivery();
        $user = $this->createUserWithRole('owner', ['whatsapp_number' => '255712345678']);

        $response = $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 (712) 345-678',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('whatsapp.otp'));
        $this->assertGuest();

        $otpPage = $this->get(route('whatsapp.otp'))
            ->assertOk()
            ->assertSee('number ending in')
            ->assertSee('5678')
            ->assertDontSee('Local development code');
        $otp = $this->sentWhatsappOtp();

        $response = $this->post(route('whatsapp.otp.verify'), ['otp' => $otp]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('owner.dashboard', absolute: false));
    }

    public function test_new_whatsapp_number_creates_an_owner_account_after_otp_verification(): void
    {
        $this->fakeWhatsappDelivery();

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'a-secure-password',
        ])->assertRedirect(route('whatsapp.otp'));

        $this->assertDatabaseMissing('users', ['whatsapp_number' => '255712345678']);
        $otpPage = $this->get(route('whatsapp.otp'))
            ->assertOk()
            ->assertSee('finish creating your account')
            ->assertSee('number ending in')
            ->assertDontSee('Local development code');
        $otp = $this->sentWhatsappOtp();

        $this->post(route('whatsapp.otp.verify'), ['otp' => $otp])
            ->assertRedirect(route('owner.dashboard', absolute: false));

        $user = User::where('whatsapp_number', '255712345678')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('a-secure-password', $user->password));
        $this->assertTrue($user->isOwner());
    }

    public function test_new_whatsapp_account_is_not_created_before_otp_verification(): void
    {
        $this->fakeWhatsappDelivery();

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'a-secure-password',
        ])->assertRedirect(route('whatsapp.otp'));

        $this->post(route('whatsapp.otp.verify'), ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['whatsapp_number' => '255712345678']);
    }

    public function test_whatsapp_login_rejects_an_incorrect_otp(): void
    {
        $this->fakeWhatsappDelivery();
        $this->createUserWithRole('owner', ['whatsapp_number' => '255712345678']);

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'password',
        ])->assertRedirect(route('whatsapp.otp'));

        $this->post(route('whatsapp.otp.verify'), ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertGuest();
    }

    public function test_expired_whatsapp_otp_requires_a_new_login(): void
    {
        $this->fakeWhatsappDelivery();
        $this->createUserWithRole('owner', ['whatsapp_number' => '255712345678']);

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'password',
        ])->assertRedirect(route('whatsapp.otp'));

        $this->travel(6)->minutes();

        $this->post(route('whatsapp.otp.verify'), ['otp' => '123456'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('whatsapp_number');

        $this->assertGuest();
    }

    public function test_whatsapp_otp_login_requires_wasender_api_configuration(): void
    {
        config()->set('services.wasender.api_token', null);
        $this->createUserWithRole('owner', ['whatsapp_number' => '255712345678']);

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'password',
        ])->assertSessionHasErrors([
            'whatsapp_number' => 'WhatsApp OTP delivery is not configured. Please configure the WASender API token.',
        ]);

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_wasender_delivery_failure_keeps_user_out_and_does_not_create_otp_challenge(): void
    {
        $this->fakeWhatsappDelivery(responseStatus: 400);
        $this->createUserWithRole('owner', ['whatsapp_number' => '255712345678']);

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => '+255 712 345 678',
            'password' => 'password',
        ])->assertSessionHasErrors('whatsapp_number');

        $this->assertGuest();
        $this->assertFalse(session()->has('auth.whatsapp_challenge'));
    }

    public function test_whatsapp_login_requires_a_whatsapp_number(): void
    {
        $this->post('/login', [
            'login_method' => 'whatsapp',
            'password' => 'password',
        ])->assertSessionHasErrors('whatsapp_number');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_tenant_cannot_authenticate_with_email_or_username(): void
    {
        $tenant = $this->createUserWithRole('tenant');

        $this->post('/login', [
            'email' => $tenant->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_tenant_cannot_authenticate_with_whatsapp(): void
    {
        $this->fakeWhatsappDelivery();
        $tenant = $this->createUserWithRole('tenant', ['whatsapp_number' => '255712345678']);

        $this->post('/login', [
            'login_method' => 'whatsapp',
            'whatsapp_number' => $tenant->whatsapp_number,
            'password' => 'password',
        ])->assertSessionHasErrors('whatsapp_number');

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_admin_can_authenticate_using_the_login_screen(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    private function fakeWhatsappDelivery(int $responseStatus = 200, array $responseBody = ['messages' => [['id' => 'wamid.test']]]): void
    {
        config()->set('services.wasender.api_token', 'unit-test-token');

        Http::fake([
            'https://wasenderapi.com/api/send-message' => Http::response($responseBody, $responseStatus),
        ]);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        Role::firstOrCreate(['name' => $role]);
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    private function sentWhatsappOtp(): string
    {
        $otp = null;

        Http::assertSent(function (ClientRequest $request) use (&$otp): bool {
            $data = $request->data();
            preg_match('/verification code is (\d{6})/', $data['text'] ?? '', $matches);
            $otp = $matches[1] ?? null;
            $authorization = $request->header('Authorization')[0] ?? '';
            $expectedAuthorization = 'Bearer'.' '.config('services.wasender.api_token');

            return $request->url() === 'https://wasenderapi.com/api/send-message'
                && $data['to'] === '255712345678'
                && hash_equals($expectedAuthorization, $authorization)
                && is_string($otp)
                && preg_match('/^\d{6}$/', $otp) === 1;
        });

        $this->assertIsString($otp);

        return $otp;
    }
}
