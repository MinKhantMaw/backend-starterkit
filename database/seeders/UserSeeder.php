<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate([
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
        ], [
            'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!')),
            'is_active' => true,
        ]);

        $superAdmin->syncRoles(['Super Admin']);
    }
}
