<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Sanctum::actingAs(User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail());
});

test('a post synchronizes categories and tags', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();

    $response = $this->postJson('/api/v1/admin/posts', [
        'title' => 'Enterprise CMS',
        'body' => 'Content',
        'category_ids' => [$category->id],
        'tag_ids' => [$tag->id],
        'status' => 'published',
    ])->assertCreated();

    $postId = $response->json('data.id');
    $this->assertDatabaseHas('category_post', ['post_id' => $postId, 'category_id' => $category->id]);
    $this->assertDatabaseHas('post_tag', ['post_id' => $postId, 'tag_id' => $tag->id]);
});
