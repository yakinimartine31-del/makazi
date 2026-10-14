<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_registration_creates_owner_accounts_only(): void
    {
        Role::firstOrCreate(['name' => 'owner']);

        $this->postJson('/api/v2/auth/register', [
            'name' => 'Owner User',
            'username' => 'owner-user',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'tenant',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);

        $this->postJson('/api/v2/auth/register', [
            'name' => 'Owner User',
            'username' => 'owner-user',
            'email' => 'owner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated()
            ->assertJsonPath('user.email', 'owner@example.com');

        $this->assertTrue(User::where('email', 'owner@example.com')->firstOrFail()->isOwner());
    }

    public function test_api_login_rejects_tenants(): void
    {
        Role::firstOrCreate(['name' => 'tenant']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->postJson('/api/v2/auth/login', [
            'email' => $tenant->email,
            'password' => 'password',
        ])->assertUnauthorized()
            ->assertJson(['message' => 'Invalid credentials']);

        $this->assertGuest();
    }

    public function test_api_login_allows_owners_and_admins(): void
    {
        Role::firstOrCreate(['name' => 'owner']);
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->postJson('/api/v2/auth/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.id', $owner->id)
            ->assertJsonStructure(['token']);
    }

    public function test_api_login_allows_admins(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->postJson('/api/v2/auth/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.id', $admin->id)
            ->assertJsonStructure(['token']);
    }
}
