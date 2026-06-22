<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'filename' => fake()->uuid().'.jpg',
            'original_name' => 'image.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => fake()->numberBetween(1000, 1000000),
            'width' => 1200,
            'height' => 800,
            'uploaded_by' => User::factory(),
        ];
    }
}
