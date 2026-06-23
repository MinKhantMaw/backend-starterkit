<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_a_super_admin_can_create_a_permission(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/permissions', ['name' => 'reports.view'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'reports.view');
    }
}
