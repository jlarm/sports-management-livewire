<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Contracts\View\View;

final class SeasonIndexController
{
    public function __invoke(): View
    {
        return view('season.index', [
            'hasSeasons' => Season::exists(),
        ]);
    }
}
