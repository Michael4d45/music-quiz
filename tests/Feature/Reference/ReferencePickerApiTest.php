<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\MusicSource;
use App\Models\SubCategory;
use App\Models\User;

test('registered user can load reference pickers for tracks and questions', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Pop']);
    $sub = SubCategory::factory()->for($category)->create(['name' => '90s']);
    MusicSource::factory()->create([
        'display_name' => 'Test Source',
        'is_active' => true,
        'priority' => 1,
    ]);

    $acting = $this->actingAs($user, 'web');

    $subs = $acting->getJson('/api/reference/sub-categories');
    $subs->assertSuccessful();
    $subs->assertJsonFragment(['id' => $sub->id]);
    expect($subs->json('sub_categories.0.label'))->toContain('Pop');
    expect($subs->json('sub_categories.0.label'))->toContain('90s');

    $sources = $acting->getJson('/api/reference/music-sources');
    $sources->assertSuccessful();
    $sources->assertJsonFragment(['label' => 'Test Source']);

    $types = $acting->getJson('/api/reference/question-types');
    $types->assertSuccessful();
    $types->assertJsonFragment(['id' => 'artist', 'label' => 'Artist']);
});

test('guest cannot load reference sub-categories', function (): void {
    $guest = User::factory()->guest()->create();

    $this->actingAs($guest, 'web')
        ->getJson('/api/reference/sub-categories')
        ->assertForbidden();
});
