<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'tenant']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
    }

    public function test_admin_can_create_a_user_with_roles(): void
    {
        $this->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Add User');

        $this->post(route('admin.users.store'), [
            'name' => 'New Tenant',
            'username' => 'new-tenant',
            'email' => 'new-tenant@example.com',
            'roles' => [Role::where('name', 'tenant')->value('id')],
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'User created successfully.');

        $user = User::where('email', 'new-tenant@example.com')->firstOrFail();
        $this->assertSame('new-tenant', $user->username);
        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertTrue($user->isTenant());
    }

    public function test_admin_can_edit_and_delete_a_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $this->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Edit User');

        $this->put(route('admin.users.update', $user), [
            'name' => 'Updated User',
            'username' => 'updated-user',
            'email' => $user->email,
            'roles' => [Role::where('name', 'owner')->value('id')],
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'User updated successfully.');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User']);
        $this->assertTrue($user->fresh()->isOwner());

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $this->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_demote_the_last_admin(): void
    {
        $this->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'username' => 'admin-user',
            'email' => $this->admin->email,
            'roles' => [Role::where('name', 'tenant')->value('id')],
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect()
            ->assertSessionHasErrors('roles');

        $this->assertTrue($this->admin->fresh()->isAdmin());
    }
}
