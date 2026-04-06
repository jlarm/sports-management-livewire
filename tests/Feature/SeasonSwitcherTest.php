<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Livewire\Livewire;

test('season switcher updates the current season in session', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $currentSeason = Season::factory()->for($organization)->create([
        'name' => 'Spring 2026',
        'active' => true,
    ]);

    $nextSeason = Season::factory()->for($organization)->create([
        'name' => 'Fall 2026',
        'active' => false,
    ]);

    Context::add('organization', $organization);
    session()->put('current_season_id', $currentSeason->id);

    Livewire::actingAs($user)
        ->test('season.switcher')
        ->set('currentSeasonId', (string) $nextSeason->id)
        ->assertHasNoErrors();

    expect(session('current_season_id'))->toBe($nextSeason->id);
});
