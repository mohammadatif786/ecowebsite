<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class NewDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
    }

    public function test_guests_are_redirected_to_login()
    {
        $response = $this->get(route('admin.new-dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_users_cannot_access_new_dashboard()
    {
        $user = User::factory()->create(['type' => 'user']);
        $this->actingAs($user);

        $response = $this->get(route('admin.new-dashboard'));
        $response->assertStatus(403);
    }

    public function test_admins_can_access_new_dashboard()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get(route('admin.new-dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Admin/NewOverview'));
    }

    public function test_admins_can_access_fee_revenue_dashboard()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get(route('admin.fee-revenue-dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Admin/FeeRevenueDashboard'));
    }

    public function test_admins_can_access_users_dashboard()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get(route('admin.users-dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Admin/UsersDashboard'));
    }
}
