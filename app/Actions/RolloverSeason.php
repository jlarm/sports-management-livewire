<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RolloverSeason
{
    public function execute(Season $sourceSeason, string $newSeasonName): Season
    {
        return DB::transaction(function () use ($sourceSeason, $newSeasonName): Season {
            $newSeason = Season::create([
                'organization_id' => $sourceSeason->organization_id,
                'name' => $newSeasonName,
                'start_date' => $sourceSeason->start_date->addYear()->toDateString(),
                'end_date' => $sourceSeason->end_date->addYear()->toDateString(),
                'active' => true,
                'is_registration_open' => false,
            ]);

            $sourceSeason->update([
                'active' => false,
            ]);

            $sourceTeams = Team::withoutGlobalScope('current_season')
                ->where('organization_id', $sourceSeason->organization_id)
                ->where('season_id', $sourceSeason->id)
                ->orderBy('id')
                ->get();

            foreach ($sourceTeams as $team) {
                Team::create([
                    'uuid' => (string) Str::uuid(),
                    'organization_id' => $team->organization_id,
                    'season_id' => $newSeason->id,
                    'division_id' => $team->division_id,
                    'name' => $team->name,
                    'slug' => $this->makeUniqueSlug($team->name, $newSeason->name),
                    'head_coach_id' => $team->head_coach_id,
                ]);
            }

            return $newSeason;
        });
    }

    private function makeUniqueSlug(string $teamName, string $seasonName): string
    {
        $baseSlug = Str::slug($teamName.' '.$seasonName);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'team';
        $candidate = $baseSlug;
        $suffix = 2;

        while (Team::withoutGlobalScopes()->where('slug', $candidate)->exists()) {
            $candidate = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
