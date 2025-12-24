<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('walks through authentication flow', function (): void {
    // Start at home page and verify initial state
    $page = visit(route('home'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Welcome')
        ->assertSee('Log in')
        ->assertSee('Sign up');

    // Click "Log in" to navigate to login page
    $page = $page
        ->click('Log in')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertSee('Sign in to your account')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertSee('create a new account')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]')
        ->assertPresent('input[name="remember-me"]')
        ->assertPresent('button[type="submit"]');

    // Click "create a new account" link to navigate to register page
    $page = $page
        ->click('create a new account')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertSee('Create your account')
        ->assertSee('Full name')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertSee('Confirm password')
        ->assertSee('sign in to existing account')
        ->assertPresent('input[name="name"]')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]')
        ->assertPresent('input[name="password_confirmation"]')
        ->assertPresent('button[type="submit"]');

    // Click "sign in to existing account" link to navigate back to login page
    $page
        ->click('sign in to existing account')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertSee('Sign in to your account')
        ->assertSee('create a new account');
});

it('can register a new user and verify user is created in database', function (): void {
    $email = 'test@example.com';
    $name = 'Test User';
    $password = 'password123';

    // Navigate to register page
    $page = visit(route('register'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Create your account');

    // Fill out registration form
    $page = $page
        ->type('input[name="name"]', $name)
        ->type('input[name="email"]', $email)
        ->type('input[name="password"]', $password)
        ->type('input[name="password_confirmation"]', $password)
        ->click('Create account')
        ->wait(2);

    // Verify user was created in database
    expect(User::where('email', $email)->first())
        ->not->toBeNull()
        ->and(User::where('email', $email)->first()->name)->toBe($name);

    // Verify page shows authenticated state (user dropdown instead of login/signup buttons)
    $page->assertSee($name)
        ->assertDontSee('Log in')
        ->assertDontSee('Sign up')
        ->screenshot(filename: 'register-success');
});

it('can login with existing user and verify page changes', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Start at home page (unauthenticated)
    $page = visit(route('home'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Log in')
        ->assertSee('Sign up')
        ->assertDontSee($user->name);

    // Navigate to login page
    $page = $page
        ->click('Log in')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertSee('Sign in to your account');

    // Fill out login form
    $page = $page
        ->type('input[name="email"]', $user->email)
        ->type('input[name="password"]', 'password')
        ->click('Sign in')
        ->wait(2);

    // Verify page shows authenticated state
    $page->assertSee($user->name)
        ->assertDontSee('Log in')
        ->assertDontSee('Sign up');
});

it('can logout and verify page view changes', function (): void {
    $user = User::factory()->create();

    // Login first
    $page = visit(route('login'))
        ->assertNoJavaScriptErrors()
        ->type('input[name="email"]', $user->email)
        ->type('input[name="password"]', 'password')
        ->click('Sign in')
        ->wait(2);

    // Verify authenticated state
    $page->assertSee($user->name)
        ->assertDontSee('Log in')
        ->assertDontSee('Sign up');

    // Click on user dropdown to open it
    $page = $page
        ->click($user->name)
        ->wait(1);

    // Click logout
    $page = $page
        ->click('Sign out')
        ->wait(2);

    // Verify page shows unauthenticated state
    $page->assertSee('Log in')
        ->assertSee('Sign up')
        ->assertDontSee($user->name);
});

it('can show validation errors on login page', function (): void {
    $page = visit(route('login'))
        ->assertNoJavaScriptErrors()
        ->type('input[name="email"]', 'nonexistent@example.com')
        ->type('input[name="password"]', 'password')
        ->click('Sign in')
        ->wait(2);

    $page->assertSee('These credentials do not match our records.');
});
