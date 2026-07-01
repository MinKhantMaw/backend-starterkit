<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        app(SettingService::class)->set('admin_2fa_enabled', false, 'boolean');
    }
}
