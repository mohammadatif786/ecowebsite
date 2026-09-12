<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminLoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'admin_security.login.max_attempts' => 2,
            'admin_security.login.decay_seconds' => 60,
            'admin_security.login.identity_max_attempts' => 50,
            'admin_security.login.identity_decay_seconds' => 60,
            'admin_security.login.ip_max_attempts' => 50,
            'admin_security.login.ip_decay_seconds' => 60,
            'admin_security.login.suspicious_attempts' => 2,
            'admin_security.sensitive_operations.max_attempts' => 3,
            'admin_security.sensitive_operations.decay_minutes' => 1,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_active_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = $this->adminUser();

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.new-dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_invalid_admin_credentials_return_generic_validation_error(): void
    {
        $admin = $this->adminUser();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();
    }

    public function test_admin_login_is_locked_after_repeated_failures(): void
    {
        Event::fake([Lockout::class]);

        $admin = $this->adminUser();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->from(route('admin.login'))->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        Event::assertDispatched(Lockout::class);
    }

    public function test_admin_can_login_after_lockout_timeout_expires(): void
    {
        $admin = $this->adminUser();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->from(route('admin.login'))->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->travel(61)->seconds();

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.new-dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_successful_admin_login_regenerates_the_session(): void
    {
        $admin = $this->adminUser();

        $this->withSession(['probe' => 'before-login']);
        $oldSessionId = session()->getId();

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = $this->adminUser(['status' => false]);

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();
        $this->assertNull($admin->fresh()->last_login_at);
    }

    public function test_non_admin_user_cannot_use_admin_login(): void
    {
        $user = User::factory()->create([
            'type' => 'user',
            'status' => true,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();
    }

    public function test_admin_login_does_not_follow_external_intended_urls(): void
    {
        $admin = $this->adminUser();

        $response = $this
            ->withSession(['url.intended' => 'https://attacker.example/phish'])
            ->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('admin.new-dashboard', absolute: false));
    }

    public function test_two_factor_and_recovery_code_endpoints_are_backend_confirmed(): void
    {
        $admin = $this->adminUser();

        $setup = $this->actingAs($admin)->postJson(route('admin.administration.two-factor.setup'));
        $setup->assertOk()->assertJsonStructure(['secret', 'demo_code', 'message']);

        $verify = $this->actingAs($admin)->postJson(route('admin.administration.two-factor.verify'), [
            'code' => $setup->json('demo_code'),
        ]);
        $verify->assertOk()->assertJson(['two_factor_enabled' => true]);

        $recovery = $this->actingAs($admin)->postJson(route('admin.administration.recovery-codes.generate'));
        $recovery->assertOk()->assertJsonCount(8, 'recovery_codes');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function adminUser(array $attributes = []): User
    {
        $admin = User::factory()->create(array_merge([
            'type' => 'admin',
            'status' => true,
        ], $attributes));

        $admin->assignRole('admin');

        return $admin;
    }
}
