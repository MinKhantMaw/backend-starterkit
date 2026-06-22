<?php

use App\Models\Page;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
    Sanctum::actingAs($admin);
});

test('a page is created with a generated unique slug', function () {
    $this->postJson('/api/v1/admin/pages', ['title' => 'About Us', 'body' => 'About'])
        ->assertCreated()->assertJsonPath('data.slug', 'about-us');

    $this->postJson('/api/v1/admin/pages', ['title' => 'About Us', 'body' => 'Another'])
        ->assertCreated()->assertJsonPath('data.slug', 'about-us-2');

    $this->assertDatabaseCount('pages', 2);
    $this->assertDatabaseHas('activity_logs', ['event' => 'created', 'subject_type' => Page::class]);
});
