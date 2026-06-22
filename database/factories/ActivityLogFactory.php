<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'event' => 'updated',
            'description' => fake()->sentence(),
            'old_values' => [],
            'new_values' => [],
            'created_at' => now(),
        ];
    }
}
