<?php

declare(strict_types=1);

use App\Http\Controllers\SeasonIndexController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('org')->group(function () {
        Route::get('seasons', SeasonIndexController::class)->name('season.index');

        Route::view('teams', 'team.index')->name('team.index');

        Route::view('organization/settings', 'organization.settings')->name('organization.settings');
    });
});

require __DIR__.'/settings.php';
