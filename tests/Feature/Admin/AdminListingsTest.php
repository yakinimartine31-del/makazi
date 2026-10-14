<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_listings(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.listings.index'))
            ->assertOk()
            ->assertSee('Manage Listings');
    }

    public function test_non_admin_cannot_view_admin_listings(): void
    {
        Role::create(['name' => 'owner']);
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('admin.listings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_users_payments_and_reports(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Users');

        $this->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Payments');

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reports');
    }

    public function test_non_admin_cannot_view_users_payments_or_reports(): void
    {
        Role::create(['name' => 'owner']);
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->get(route('admin.payments.index'))
            ->assertForbidden();

        $this->get(route('admin.reports.index'))
            ->assertForbidden();
    }
}
