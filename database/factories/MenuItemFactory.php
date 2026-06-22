<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
