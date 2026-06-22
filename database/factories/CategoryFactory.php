<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'slug' => fake()->unique()->slug(), 'description' => fake()->sentence(), 'sort_order' => 0];
    }
}
