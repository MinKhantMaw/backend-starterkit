<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(5), 'slug' => fake()->unique()->slug(), 'excerpt' => fake()->paragraph(), 'body' => fake()->paragraphs(5, true), 'status' => 'draft', 'created_by' => User::factory(), 'updated_by' => null];
    }
}
