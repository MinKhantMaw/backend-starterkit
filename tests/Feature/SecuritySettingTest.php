<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecuritySettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_admin_can_view_security_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/security-settings')
            ->assertOk()
            ->assertJsonPath('message', 'Security settings retrieved successfully.')
            ->assertJsonPath('data.admin_2fa_enabled', false);
    }

    public function test_super_admin_can_enable_global_2fa(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $setup = $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/security-settings/2fa/setup')
            ->assertOk()
            ->assertJsonStructure(['data' => ['qr_code_svg', 'secret']])
            ->assertJsonPath('data.qr_code_svg', fn (string $svg) => str_starts_with($svg, '<?xml') || str_starts_with($svg, '<svg'))
            ->json('data');

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/security-settings/2fa/confirm', [
                'code' => app(Google2FA::class)->getCurrentOtp($setup['secret']),
            ])->assertOk()
            ->assertJsonPath('data.admin_2fa_enabled', true);

        $this->assertTrue(app(SettingService::class)->getBoolean('admin_2fa_enabled'));
    }

    public function test_non_super_admin_cannot_update_security_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/security-settings', ['admin_2fa_enabled' => false])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/security-settings/2fa/setup')
            ->assertForbidden();
    }

    public function test_disable_2fa_requires_password(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        app(SettingService::class)->set('admin_2fa_enabled', true, 'boolean');

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/security-settings/2fa/disable', [
                'password' => 'wrong-password',
            ])->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'The current password is incorrect.');

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/security-settings/2fa/disable', [
                'password' => env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!'),
            ])->assertOk()
            ->assertJsonPath('data.admin_2fa_enabled', false);
    }

    public function test_security_setting_update_cannot_enable_without_otp_confirmation(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson('/api/v1/security-settings', ['admin_2fa_enabled' => true])
            ->assertUnprocessable()
            ->assertJsonPath('errors.admin_2fa_enabled.0', 'Use the 2FA setup and confirm endpoints to enable global admin 2FA.');
    }

    public function test_super_admin_role_has_security_setting_permissions(): void
    {
        $superAdmin = Role::findByName('Super Admin');

        $this->assertTrue($superAdmin->hasPermissionTo('security-setting.view'));
        $this->assertTrue($superAdmin->hasPermissionTo('security-setting.update'));
    }
}
