<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingService::class);

        $settings->set('max_login_attempts', 5, 'integer');
        $settings->set('lock_account_enabled', true, 'boolean');
        $settings->set('login_rate_limit_enabled', true, 'boolean');
        $settings->set('remember_me_enabled', true, 'boolean');
        $settings->set('password_history_count', 5, 'integer');
        $settings->set('password_expiry_days', 90, 'integer');
        $settings->set('force_password_change_enabled', false, 'boolean');
        $settings->set('admin_2fa_enabled', false, 'boolean');
    }
}
