<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;

final class TeamIndexController
{
    public function __invoke()
    {
        return view('team.index', [
            'hasTeams' => Team::exists(),
        ]);
    }
}
