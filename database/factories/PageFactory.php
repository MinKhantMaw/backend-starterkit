<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return ['title' => $title, 'slug' => fake()->unique()->slug(), 'body' => fake()->paragraphs(3, true), 'status' => 'draft', 'created_by' => User::factory(), 'updated_by' => null];
    }
}
