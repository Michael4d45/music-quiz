<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access home page', function (): void {
    $response = $this->get(route('home'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('home'));
});

it('can access login page', function (): void {
    $response = $this->get(route('login'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('auth/login'));
});

it('can access register page', function (): void {
    $response = $this->get(route('register'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('auth/register'));
});

it('can access profile page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('profile'));
});

it('can access preferences page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('preferences'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('preferences'));
});
