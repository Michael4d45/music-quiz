<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->group(function () {});

Route::get('home', \App\Actions\Home\ShowHome::class);

require __DIR__ . '/auth.php';
