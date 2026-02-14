<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
])->group(function () {
    // Authenticated routes for user-specific data and actions
});

Route::middleware([])->group(function () {
    // Routes that allow unauthenticated access for public data
});

require __DIR__ . '/auth.php';
