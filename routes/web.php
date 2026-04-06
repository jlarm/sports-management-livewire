<?php

declare(strict_types=1);

use App\Http\Controllers\SeasonIndexController;
use App\Http\Controllers\TeamIndexController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('org')->group(function () {
        Route::get('seasons', SeasonIndexController::class)->name('season.index');

        Route::get('teams', TeamIndexController::class)->name('team.index');
        Route::view('teams/create', 'team.create')->name('team.create');

        Route::view('organization/settings', 'organization.settings')->name('organization.settings');
    });
});

require __DIR__.'/settings.php';
