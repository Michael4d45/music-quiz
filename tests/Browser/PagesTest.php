<?php

declare(strict_types=1);

use App\Models\User;

it('home page loads without JS errors', function (): void {
    visit(route('home'))
        ->assertNoJavaScriptErrors();
});

it('login page loads without JS errors', function (): void {
    visit(route('login'))
        ->assertNoJavaScriptErrors();
});

it('register page loads without JS errors', function (): void {
    visit(route('register'))
        ->assertNoJavaScriptErrors();
});

it('profile page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit(route('profile'))
        ->assertNoJavaScriptErrors();
});

it('preferences page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit(route('preferences'))
        ->assertNoJavaScriptErrors();
});
