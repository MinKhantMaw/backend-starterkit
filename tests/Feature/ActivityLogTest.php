<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_activity_logs_list_works(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        ActivityLog::query()->create([
            'actor_id' => $superAdmin->id,
            'event' => 'created',
            'action' => 'created',
            'module' => 'Feature Test',
            'subject_type' => User::class,
            'subject_id' => $superAdmin->id,
            'description' => 'Created Feature Test #'.$superAdmin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test',
        ]);

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/activity-logs?module=Feature%20Test&sort_by=created_at&sort_direction=desc&perPage=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.user_id', $superAdmin->id)
            ->assertJsonPath('data.items.0.action', 'created')
            ->assertJsonPath('data.items.0.module', 'Feature Test')
            ->assertJsonPath('data.meta.per_page', 10);
    }
}
