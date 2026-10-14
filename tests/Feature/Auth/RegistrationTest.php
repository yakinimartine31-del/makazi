<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Role::create(['name' => 'owner']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'test-user',
            'email' => 'test@example.com',
            'whatsapp_number' => '+255 712-345-678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('owner.dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'whatsapp_number' => '255712345678',
        ]);
        $this->assertTrue(auth()->user()->isOwner());
    }

    public function test_whatsapp_number_must_be_unique_after_normalization(): void
    {
        User::factory()->create(['whatsapp_number' => '255712345678']);
        Role::create(['name' => 'owner']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Another User',
                'username' => 'another-user',
                'email' => 'another@example.com',
                'whatsapp_number' => '+255 (712) 345-678',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'tenant',
            ])
            ->assertSessionHasErrors('whatsapp_number', 'role');
    }

    public function test_tenant_role_cannot_be_selected_during_registration(): void
    {
        Role::create(['name' => 'owner']);

        $this->post('/register', [
            'name' => 'Tenant Attempt',
            'username' => 'tenant-attempt',
            'email' => 'tenant@example.com',
            'whatsapp_number' => '+255 713 222 111',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'tenant',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'tenant@example.com']);
    }
}
