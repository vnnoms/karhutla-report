<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('/reporter', [DashboardController::class, 'reporter'])
            ->middleware('role:reporter')
            ->name('reporter');
        Route::get('/volunteer', [DashboardController::class, 'volunteer'])
            ->middleware('role:volunteer')
            ->name('volunteer');
        Route::get('/coordinator', [DashboardController::class, 'coordinator'])
            ->middleware('role:coordinator')
            ->name('coordinator');
    });
});
