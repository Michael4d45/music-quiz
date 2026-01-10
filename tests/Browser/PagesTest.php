<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    setup_log_capture('pages.log');
});

afterEach(function () {
    assert_no_log_errors(storage_path('logs/pages.log'));
});

it('home page loads without JS errors', function (): void {
    visit('/')->assertNoJavaScriptErrors();
});

it('login page loads without JS errors', function (): void {
    visit('/login')->assertNoJavaScriptErrors();
});

it('register page loads without JS errors', function (): void {
    visit('/register')->assertNoJavaScriptErrors();
});

it('profile page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/profile')->assertNoJavaScriptErrors();
});

it('displays 404 error page with expected content', function (): void {
    visit('/non-existent-route')
        ->assertNoJavaScriptErrors()
        ->assertSee('404')
        ->assertSee('Page Not Found')
        ->assertSee('Sorry, the page you are looking for could not be found.')
        ->assertSee('Go Home')
        ->assertSee('Go Back');
});

it('can navigate home from 404 page', function (): void {
    visit('/non-existent-route')
        ->assertNoJavaScriptErrors()
        ->assertSee('404')
        ->assertSee('Page Not Found')
        ->click('Go Home')
        ->wait(1)
        ->assertPathIs('/')
        ->assertNoJavaScriptErrors();
});

it('can register a new user', function (): void {
    visit('/register')
        ->assertNoJavaScriptErrors()
        ->waitForText('Create Account', 5)
        ->assertSee('Create Account')
        ->assertSee('Full Name')
        ->assertSee('Email')
        ->assertSee('Password');
});

it('can login with valid credentials', function (): void {
    visit('/login')
        ->assertNoJavaScriptErrors()
        ->waitForText('Login', 5)
        ->assertSee('Login')
        ->assertSee('Email')
        ->assertSee('Password');
});

it('shows offline warning on login page when offline', function (): void {
    visit('/login')->waitForText('Login', 5)->assertSee('Login');
});

it('shows offline warning on register page when offline', function (): void {
    visit('/register')
        ->waitForText('Create Account', 5)
        ->assertSee('Create Account');
});

it('login form validation works', function (): void {
    visit('/login')->waitForText('Login', 5)->assertSee('Login');
});

it('register form validation works', function (): void {
    visit('/register')
        ->waitForText('Create Account', 5)
        ->assertSee('Create Account');
});

it('music tracks page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/music-tracks')->assertNoJavaScriptErrors();
});

it('quiz questions page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/quiz-questions')->assertNoJavaScriptErrors();
});

it('active games page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/active-games')->assertNoJavaScriptErrors();
});

it('create session page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/sessions/create')->assertNoJavaScriptErrors();
});

it('statistics page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/statistics')->assertNoJavaScriptErrors();
});

it('browse tracks page loads without JS errors', function (): void {
    visit('/browse/tracks')->assertNoJavaScriptErrors();
});

it('browse public playlists page loads without JS errors', function (): void {
    visit('/browse/playlists')->assertNoJavaScriptErrors();
});

it('join session page loads without JS errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    visit('/sessions/join')->assertNoJavaScriptErrors();
});

it('can navigate through all sidebar menu items without 404 errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Start from home page
    $page = visit('/')->assertNoJavaScriptErrors();

    // Test Browse section
    $page->click('Browse')->waitForText('Browse', 5)->assertNoJavaScriptErrors();
    $page->click('Categories')->waitForText('Categories', 5)->assertNoJavaScriptErrors();
    $page->click('Tracks')->waitForText('Browse Tracks', 5)->assertNoJavaScriptErrors();
    $page->click('Public Playlists')->waitForText('Public Playlists', 5)->assertNoJavaScriptErrors();

    // Test Games section
    $page->click('Active Games')->waitForText('Active Games', 5)->assertNoJavaScriptErrors();
    $page->click('Create Game')->waitForText('Create New Game', 5)->assertNoJavaScriptErrors();
    $page->click('Join Game')->waitForText('Join a Game', 5)->assertNoJavaScriptErrors();

    // Test Playlists section
    $page->click('My Playlists')->waitForText('My Playlists', 5)->assertNoJavaScriptErrors();
    $page->click('My Music Tracks')->waitForText('My Music Tracks', 5)->assertNoJavaScriptErrors();
    $page->click('My Quiz Questions')->waitForText('My Quiz Questions', 5)->assertNoJavaScriptErrors();

    // Test Statistics section
    $page->click('My Statistics')->waitForText('My Statistics', 5)->assertNoJavaScriptErrors();
    $page->click('Leaderboard')->waitForText('Leaderboard', 5)->assertNoJavaScriptErrors();
});
