<?php

declare(strict_types=1);

use App\Features\Auth\Actions\ShowUser;
use Illuminate\Support\Facades\Route;

Route::get('user', ShowUser::class);
