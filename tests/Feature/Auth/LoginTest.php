<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('can login a user and establishes session', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $csrfToken = csrf_token();
    $response = $this->withSession([
        '_token' => $csrfToken,
    ])->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
        '_token' => $csrfToken,
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
        ]);

    // User should be session-authenticated after login
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $csrfToken = csrf_token();
    $response = $this->withSession([
        '_token' => $csrfToken,
    ])->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        '_token' => $csrfToken,
    ]);

    $response->assertStatus(422);

    // EnsureGuestUser middleware means a guest is always logged in,
    // so we verify the real user was NOT authenticated
    $currentUser = auth()->user();
    expect($currentUser)->not->toBeNull();
    expect($currentUser->is_guest)->toBeTrue();
});

it('rate limits login attempts', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $csrfToken = csrf_token();

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->withSession([
            '_token' => $csrfToken,
        ])->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            '_token' => $csrfToken,
        ]);
    }

    // 6th attempt should be rate limited
    $response = $this->withSession([
        '_token' => $csrfToken,
    ])->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        '_token' => $csrfToken,
    ]);

    $response->assertStatus(422);
    expect($response->json('message'))->toContain('Too many login attempts');
});

it('browser: login -> csrf-cookie -> user with real cookies and referer', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $origin = config('app.url'); // http://127.0.0.1:8000

    // Step 0: Initial CSRF cookie fetch (like page load)
    $initCsrf = $this->withHeaders([
        'Referer' => $origin . '/',
        'Origin' => $origin,
    ])->get('/sanctum/csrf-cookie');
    $initCsrf->assertStatus(204);
    $cookies = extractCookiesFromResponse($initCsrf);

    // Step 0b: Initial GET /api/user (AuthContext useEffect on mount)
    // This stores password_hash_web = HMAC(null) in the session via Sanctum's
    // AuthenticateSession middleware (guest user has null password).
    $initialUserResponse = $this->withHeaders([
        'Referer' => $origin . '/',
        'Origin' => $origin,
        'X-XSRF-TOKEN' => $cookies['XSRF-TOKEN'] ?? '',
    ])->withUnencryptedCookies($cookies)->getJson('/api/user');
    $initialUserResponse->assertStatus(200);
    expect($initialUserResponse->json('is_guest'))->toBeTrue();
    $cookies = array_merge($cookies, extractCookiesFromResponse($initialUserResponse));

    // Reset guards — in production each request is a fresh PHP process, but in
    // tests the app instance is reused. auth:sanctum calls Auth::shouldUse('sanctum')
    // which changes the default driver and makes Auth::attempt() target the
    // RequestGuard (which lacks attempt()). Reset both the cached guards AND the
    // default driver to simulate a fresh process.
    Auth::forgetGuards();
    Auth::setDefaultDriver('web');

    // Step 1: POST /login
    $loginResponse = $this->withHeaders([
        'Referer' => $origin . '/login',
        'Origin' => $origin,
        'X-XSRF-TOKEN' => $cookies['XSRF-TOKEN'] ?? '',
    ])->withUnencryptedCookies($cookies)->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);
    $loginResponse->assertStatus(200)->assertJson(['message' => 'Authentication successful']);
    $cookies = array_merge($cookies, extractCookiesFromResponse($loginResponse));

    // Step 2: GET /sanctum/csrf-cookie (frontend ensureCsrfToken after login)
    $csrfResponse = $this->withHeaders([
        'Referer' => $origin . '/login',
        'Origin' => $origin,
    ])->withUnencryptedCookies($cookies)->get('/sanctum/csrf-cookie');
    $csrfResponse->assertStatus(204);
    $cookies = array_merge($cookies, extractCookiesFromResponse($csrfResponse));

    Auth::forgetGuards();
    Auth::setDefaultDriver('web');

    // Step 3: GET /api/user (AuthContext getUser after login)
    // Before the fix, AuthenticateSession would see password_hash_web = HMAC(null)
    // from the guest, compare against the real user's HMAC(bcrypt), mismatch → 401.
    $userResponse = $this->withHeaders([
        'Referer' => $origin . '/',
        'Origin' => $origin,
        'X-XSRF-TOKEN' => $cookies['XSRF-TOKEN'] ?? '',
    ])->withUnencryptedCookies($cookies)->getJson('/api/user');

    $userResponse->assertStatus(200);
    expect($userResponse->json('id'))->toBe($user->id);
    expect($userResponse->json('email'))->toBe('test@example.com');
    expect($userResponse->json('is_guest'))->toBeFalse();
});

/**
 * Helper: extract cookies from a TestResponse's Set-Cookie headers.
 * Returns plain (decrypted) cookie name => value pairs.
 */
function extractCookiesFromResponse(\Illuminate\Testing\TestResponse $response): array
{
    $cookies = [];
    $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);

    foreach ($response->headers->getCookies() as $cookie) {
        $name = $cookie->getName();
        $value = $cookie->getValue();

        if ($value === null || $value === '') {
            continue;
        }

        // Try to decrypt the cookie value
        try {
            $decrypted = $encrypter->decrypt($value, false);
            $cookies[$name] = $decrypted;
        } catch (\Throwable) {
            // Cookie might not be encrypted (like XSRF-TOKEN)
            $cookies[$name] = $value;
        }
    }

    return $cookies;
}
