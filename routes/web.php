<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinatorReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportRecapController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VolunteerReportController;
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

    Route::get('/coordinator/reports/recap', [ReportRecapController::class, 'index'])
        ->middleware('role:coordinator')
        ->name('coordinator.reports.recap');

    Route::get('/coordinator/reports/{report}', [CoordinatorReportController::class, 'show'])
        ->whereNumber('report')
        ->middleware('role:coordinator')
        ->name('coordinator.reports.show');
    Route::get('/coordinator/reports/{report}/evidence', [CoordinatorReportController::class, 'evidence'])
        ->whereNumber('report')
        ->middleware('role:coordinator')
        ->name('coordinator.reports.evidence');
    Route::get('/coordinator/reports/{report}/forwardings', [CoordinatorReportController::class, 'forwardings'])
        ->whereNumber('report')
        ->middleware('role:coordinator')
        ->name('coordinator.reports.forwardings.index');
    Route::post('/coordinator/reports/{report}/forwardings', [CoordinatorReportController::class, 'storeForwarding'])
        ->whereNumber('report')
        ->middleware('role:coordinator')
        ->name('coordinator.reports.forwardings.store');
    Route::post('/coordinator/reports/{report}/complete', [CoordinatorReportController::class, 'complete'])
        ->whereNumber('report')
        ->middleware('role:coordinator')
        ->name('coordinator.reports.complete');

    Route::prefix('volunteer/reports')
        ->name('volunteer.reports.')
        ->middleware('role:volunteer')
        ->controller(VolunteerReportController::class)
        ->group(function (): void {
            Route::get('/{report}', 'show')->name('show');
            Route::get('/{report}/evidence', 'evidence')->name('evidence');
            Route::post('/{report}/review', 'storeReview')->name('reviews.store');
        });

    Route::prefix('coordinator/users')
        ->name('coordinator.users.')
        ->middleware('role:coordinator')
        ->controller(UserManagementController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}', 'show')->name('show');
            Route::get('/{user}/role', 'editRole')->name('role.edit');
            Route::put('/{user}/role', 'updateRole')->name('role.update');
        });

    Route::prefix('reporter/reports')
        ->name('reporter.reports.')
        ->middleware('role:reporter')
        ->controller(ReportController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{report}/edit', 'edit')->name('edit');
            Route::put('/{report}', 'update')->name('update');
            Route::delete('/{report}', 'destroy')->name('destroy');
            Route::get('/{report}/evidence', 'evidence')->name('evidence');
            Route::get('/{report}', 'show')->name('show');
        });

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
