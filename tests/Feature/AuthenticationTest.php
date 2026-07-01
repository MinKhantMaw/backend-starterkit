<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\SecuritySetting\Services\SecuritySettingService;
use App\Services\SettingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_an_active_user_can_authenticate_with_sanctum(): void
    {
        app(SettingService::class)->set('admin_2fa_enabled', false, 'boolean');
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_2fa', false)
            ->assertJsonStructure(['data' => ['token', 'access_token', 'token_type', 'requires_2fa', 'user']]);
    }

    public function test_login_requires_otp_when_global_admin_2fa_is_enabled(): void
    {
        $this->enableGlobalTwoFactor();
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Two-factor authentication required.')
            ->assertJsonPath('data.requires_2fa', true)
            ->assertJsonMissingPath('data.token')
            ->assertJsonMissingPath('data.access_token')
            ->assertJsonStructure(['data' => ['temporary_token', 'user' => ['email']]]);
    }

    public function test_valid_otp_challenge_returns_sanctum_token(): void
    {
        $secret = $this->enableGlobalTwoFactor();
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $temporaryToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->json('data.temporary_token');

        $this->postJson('/api/v1/auth/2fa/challenge', [
            'temporary_token' => $temporaryToken,
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
        ])->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.requires_2fa', false)
            ->assertJsonStructure(['data' => ['token', 'access_token', 'token_type', 'user']]);
    }

    public function test_invalid_otp_challenge_fails(): void
    {
        $this->enableGlobalTwoFactor();
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $temporaryToken = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->json('data.temporary_token');

        $this->postJson('/api/v1/auth/2fa/challenge', [
            'temporary_token' => $temporaryToken,
            'code' => '000000',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid two-factor authentication code.');
    }

    public function test_random_credentials_cannot_authenticate(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'random-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_existing_user_with_wrong_password_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_an_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!', 'is_active' => false]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your account is inactive. Please contact administrator.');
    }

    public function test_protected_routes_require_a_sanctum_token(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    private function enableGlobalTwoFactor(): string
    {
        $setup = app(SecuritySettingService::class)->setup();
        $code = app(Google2FA::class)->getCurrentOtp($setup['secret']);

        app(SecuritySettingService::class)->confirm($code);

        return $setup['secret'];
    }
}
