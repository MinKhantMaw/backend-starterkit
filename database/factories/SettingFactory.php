<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => fake()->unique()->slug(),
            'value' => fake()->sentence(),
            'type' => 'string',
            'is_public' => false,
        ];
    }
}
