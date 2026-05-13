<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can register a new user', function (): void {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $csrfToken = csrf_token();
    $response = $this->withSession([
        '_token' => $csrfToken,
    ])->postJson('/register', array_merge($data, [
        '_token' => $csrfToken,
    ]));

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('John Doe');

    // User should be session-authenticated after registration
    $this->assertAuthenticatedAs($user);
});

it('upgrades an existing guest user instead of creating a duplicate', function (): void {
    $guest = User::factory()->guest()->create();
    $this->actingAs($guest, 'web');

    $csrfToken = csrf_token();
    $response = $this->withSession([
        '_token' => $csrfToken,
    ])->postJson(
        '/register',
        [
            'name' => 'Upgraded Guest',
            'email' => 'upgraded@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_token' => $csrfToken,
        ],
    );

    $response->assertSuccessful();

    $guest->refresh();
    expect($guest->is_guest)->toBeFalse();
    expect($guest->email)->toBe('upgraded@example.com');
    expect($guest->name)->toBe('Upgraded Guest');
    expect(User::query()->count())->toBe(1);
});

it('allows stateful API user fetch after guest upgrade registration', function (): void {
    $guest = User::factory()->guest()->create();
    $this->actingAs($guest, 'web');

    $csrfToken = csrf_token();
    $this->withSession([
        '_token' => $csrfToken,
    ])
        ->postJson('/register', [
            'name' => 'Stateful Guest',
            'email' => 'stateful-guest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_token' => $csrfToken,
        ])
        ->assertSuccessful();

    $this->getJson('/api/user')->assertSuccessful();
});
