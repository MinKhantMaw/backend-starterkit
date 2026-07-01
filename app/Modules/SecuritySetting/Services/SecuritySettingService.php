<?php

namespace App\Modules\SecuritySetting\Services;

use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;

class SecuritySettingService
{
    private const ENABLED_KEY = 'admin_2fa_enabled';

    private const SECRET_KEY = 'admin_2fa_secret';

    private const DEFAULTS = [
        'max_login_attempts' => 5,
        'lock_account_enabled' => true,
        'login_rate_limit_enabled' => true,
        'remember_me_enabled' => true,
        'password_history_count' => 5,
        'password_expiry_days' => 90,
        'force_password_change_enabled' => false,
        self::ENABLED_KEY => false,
    ];

    private readonly Google2FA $google2fa;

    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogService $activityLogs,
    ) {
        $this->google2fa = new Google2FA(new Bacon(new SvgImageBackEnd));
    }

    public function settings(): array
    {
        return collect(self::DEFAULTS)
            ->mapWithKeys(fn (mixed $default, string $key) => [$key => is_bool($default)
                ? $this->settings->getBoolean($key, $default)
                : (int) $this->settings->get($key, $default)])
            ->all();
    }

    public function isAdminTwoFactorEnabled(): bool
    {
        return $this->settings->getBoolean(self::ENABLED_KEY);
    }

    public function update(User $user, array $data): array
    {
        if (($data[self::ENABLED_KEY] ?? false) === true && ! $this->isAdminTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                self::ENABLED_KEY => ['Use the 2FA setup and confirm endpoints to enable global admin 2FA.'],
            ]);
        }

        foreach ($data as $key => $value) {
            $this->settings->set($key, $value, is_bool(self::DEFAULTS[$key] ?? null) ? 'boolean' : 'integer');
        }

        $this->activityLogs->recordSecuritySettingUpdated($user, $this->settings());

        return $this->settings();
    }

    public function setup(): array
    {
        $secret = $this->getOrCreateSecret();
        $qrCode = $this->google2fa->getQRCodeInline(
            config('app.name', 'Laravel Admin'),
            'Admin 2FA',
            $secret,
        );

        return [
            'qr_code_svg' => $this->svgFromInlineQrCode($qrCode),
            'secret' => $secret,
        ];
    }

    public function confirm(User|string $user, ?string $code = null): array
    {
        if (is_string($user)) {
            $code = $user;
            $user = auth()->user();
        }

        if (! $this->verifyCode($code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided two-factor authentication code is invalid.'],
            ]);
        }

        $this->settings->set(self::ENABLED_KEY, true, 'boolean');

        if ($user instanceof User) {
            $this->activityLogs->recordSecuritySettingUpdated($user, $this->settings());
        }

        return $this->settings();
    }

    public function disable(User $user, string $password): array
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The current password is incorrect.'],
            ]);
        }

        $this->settings->set(self::ENABLED_KEY, false, 'boolean');
        $this->activityLogs->recordSecuritySettingUpdated($user, $this->settings());

        return $this->settings();
    }

    public function verifyCode(string $code): bool
    {
        $secret = $this->secret();

        return $secret !== null && $this->google2fa->verifyKey($secret, $code, 1);
    }

    public function secret(): ?string
    {
        $encrypted = $this->settings->get(self::SECRET_KEY);

        if (! $encrypted) {
            return null;
        }

        return Crypt::decryptString($encrypted);
    }

    private function getOrCreateSecret(): string
    {
        $secret = $this->secret();

        if ($secret !== null) {
            return $secret;
        }

        $secret = $this->google2fa->generateSecretKey(32);
        $this->settings->set(self::SECRET_KEY, Crypt::encryptString($secret), 'encrypted');

        return $secret;
    }

    private function svgFromInlineQrCode(string $qrCode): string
    {
        if (str_starts_with($qrCode, 'data:image/svg+xml;base64,')) {
            return base64_decode(substr($qrCode, strlen('data:image/svg+xml;base64,'))) ?: $qrCode;
        }

        return $qrCode;
    }
}
