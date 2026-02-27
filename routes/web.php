<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Src\Contexts\Auth\Infrastructure\Adapters\Http\Controllers\Web\OtpController;
use Src\Contexts\Auth\Infrastructure\Adapters\Http\Controllers\Web\SocialiteController;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Web\UserPageController;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Api\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return Inertia::render('auth/LoginPage');
})->name('login');

// ── Authenticated Routes ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard/DashboardPage');
    })->name('dashboard');

    Route::get('/profile', function () {
        return Inertia::render('profile/ProfilePage');
    })->name('profile');
});
