<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SchoolSelectionController;

// Startpagina
Route::get('/', function () {
    return redirect()->route('login');
});

// Loginpagina en aanmelden
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

    Route::get('/s/{slug}/login', [
        LoginController::class,
        'showSchoolLogin'
    ])->name('school.login');
});

// Dashboard: alleen toegankelijk na aanmelden
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })
    ->middleware('active.school')
    ->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/schools/select', [
        SchoolSelectionController::class,
        'index'
    ])->name('schools.select');

    Route::post('/schools/select', [
        SchoolSelectionController::class,
        'select'
    ])->name('schools.select.store');

});


// Centraal platformbeheer
Route::middleware(['auth', 'platform.admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {

        Route::get('/', function () {
            return view('platform.dashboard');
        })->name('dashboard');

    });
