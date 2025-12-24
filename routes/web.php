<?php

declare(strict_types=1);

use App\Actions\Auth\ConfirmPassword;
use App\Actions\Auth\Login;
use App\Actions\Auth\Logout;
use App\Actions\Auth\Register;
use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\SendEmailVerificationNotification;
use App\Actions\Auth\SendPasswordResetLink;
use App\Actions\Auth\ShowConfirmPassword;
use App\Actions\Auth\ShowEmailVerificationPrompt;
use App\Actions\Auth\ShowForgotPassword;
use App\Actions\Auth\ShowLogin;
use App\Actions\Auth\ShowRegister;
use App\Actions\Auth\ShowResetPassword;
use App\Actions\Auth\UpdatePassword;
use App\Actions\Auth\VerifyEmail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home');
})->name('home');

// Guest routes
Route::middleware('guest')->group(function (): void {
    Route::get('/login', ShowLogin::class)->name('login');
    Route::post('/login', Login::class)->name('login.store');

    Route::get('/register', ShowRegister::class)->name('register');
    Route::post('/register', Register::class)->name('register.store');

    Route::get('/forgot-password', ShowForgotPassword::class)->name('password.request');
    Route::post('/forgot-password', SendPasswordResetLink::class)->name('password.email');

    Route::get('/reset-password/{token}', ShowResetPassword::class)->name('password.reset');
    Route::post('/reset-password', ResetPassword::class)->name('password.store');
});

// Auth routes
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', Logout::class)->name('logout');

    Route::get('/profile', function () {
        return Inertia::render('profile');
    })->name('profile');

    Route::get('/preferences', function () {
        return Inertia::render('preferences');
    })->name('preferences');

    Route::put('/password', UpdatePassword::class)->name('password.update');

    Route::get('/confirm-password', ShowConfirmPassword::class)->name('password.confirm');
    Route::post('/confirm-password', ConfirmPassword::class)->name('password.confirm.store');

    Route::get('/email/verify', ShowEmailVerificationPrompt::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmail::class)->name('verification.verify');
    Route::post('/email/verification-notification', SendEmailVerificationNotification::class)->name('verification.send');
});
