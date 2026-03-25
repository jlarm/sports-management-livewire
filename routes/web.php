<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('org')->group(function () {
        Route::view('seasons', 'season.index')->name('season.index');

        Route::view('organization/settings', 'organization.settings')->name('organization.settings');
    });
});

require __DIR__.'/settings.php';
