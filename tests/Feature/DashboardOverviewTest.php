<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_view_dashboard_overview(): void
    {
        User::factory()->count(3)->create(['is_active' => true]);
        User::factory()->count(2)->create(['is_active' => false]);

        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Dashboard overview retrieved.')
            ->assertJsonPath('data.total_users', 6)
            ->assertJsonPath('data.active_users', 4)
            ->assertJsonPath('data.inactive_users', 2)
            ->assertJsonPath('data.total_roles', 4)
            ->assertJsonPath('data.recent_users', fn (array $users) => count($users) === 5)
            ->assertJsonStructure([
                'data' => [
                    'total_users',
                    'active_users',
                    'inactive_users',
                    'total_roles',
                    'total_permissions',
                    'recent_users',
                ],
            ]);
    }

    public function test_dashboard_overview_requires_authentication(): void
    {
        $this->getJson('/api/v1/dashboard/overview')
            ->assertUnauthorized();
    }
}
