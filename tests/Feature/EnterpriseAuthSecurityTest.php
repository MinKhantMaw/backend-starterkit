<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use App\Modules\SecuritySetting\Services\SecuritySettingService;
use App\Services\SettingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class EnterpriseAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_five_failed_attempts_lock_account(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        for ($i = 1; $i <= 4; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Your account has been locked because of too many failed login attempts. Please contact administrator.');

        $user->refresh();

        $this->assertFalse($user->is_active);
        $this->assertSame(5, $user->failed_login_attempts);
        $this->assertNotNull($user->last_failed_login_at);
        $this->assertNotNull($user->locked_at);
    }

    public function test_successful_login_resets_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPassword123!',
            'failed_login_attempts' => 3,
            'last_failed_login_at' => now(),
            'locked_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk();

        $user->refresh();

        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->last_failed_login_at);
        $this->assertNull($user->locked_at);
    }

    public function test_admin_unlock_resets_attempts(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $user = User::factory()->create([
            'is_active' => false,
            'failed_login_attempts' => 5,
            'last_failed_login_at' => now(),
            'locked_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/users/{$user->id}/status", ['status' => 'active'])
            ->assertOk()
            ->assertJsonPath('data.failed_login_attempts', 0)
            ->assertJsonPath('data.locked_at', null);

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->last_failed_login_at);
        $this->assertNull($user->locked_at);
    }

    public function test_password_history_prevents_reuse(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'StrongPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])->assertOk();

        $user->refresh();
        $user->forceFill(['password' => Hash::make('AnotherPassword123!')])->save();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'AnotherPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'You cannot reuse one of your last 5 passwords.');
    }

    public function test_login_history_records_success_and_failure(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk();

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'email' => $user->email,
            'success' => false,
            'failure_reason' => 'invalid_credentials',
        ]);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'email' => $user->email,
            'success' => true,
            'failure_reason' => null,
        ]);
    }

    public function test_logout_all_devices_revokes_sessions(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk()->json('data.access_token');

        $this->assertSame(1, UserSession::where('user_id', $user->id)->whereNull('logged_out_at')->count());

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout-all')
            ->assertOk();

        $this->assertSame(0, $user->fresh()->tokens()->count());
        $this->assertSame(0, UserSession::where('user_id', $user->id)->whereNull('logged_out_at')->count());
    }

    public function test_global_2fa_still_requires_otp_before_token(): void
    {
        $setup = app(SecuritySettingService::class)->setup();
        app(SettingService::class)->set('admin_2fa_enabled', true, 'boolean');
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $temporaryToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk()
            ->assertJsonPath('data.requires_2fa', true)
            ->assertJsonMissingPath('data.access_token')
            ->json('data.temporary_token');

        $this->postJson('/api/v1/auth/2fa/challenge', [
            'temporary_token' => $temporaryToken,
            'code' => app(Google2FA::class)->getCurrentOtp($setup['secret']),
        ])->assertOk()
            ->assertJsonPath('data.requires_2fa', false)
            ->assertJsonStructure(['data' => ['access_token']]);
    }

    public function test_security_settings_update_requires_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/security-settings', [
                'max_login_attempts' => 4,
            ])->assertForbidden();

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson('/api/v1/security-settings', [
                'max_login_attempts' => 4,
                'password_history_count' => 6,
            ])->assertOk()
            ->assertJsonPath('data.max_login_attempts', 4)
            ->assertJsonPath('data.password_history_count', 6);
    }
}
