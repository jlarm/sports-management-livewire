<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Division;
use App\Models\Organization;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Livewire\Livewire;

test('season rollover creates a new season, retires the current one, and duplicates teams', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $currentSeason = Season::factory()->for($organization)->create([
        'name' => 'Spring 2026',
        'start_date' => '2026-03-01',
        'end_date' => '2026-07-01',
        'active' => true,
    ]);

    $division = Division::factory()->for($organization)->create();
    $headCoach = User::factory()->create();

    Team::factory()->for($organization)->for($currentSeason)->for($division)->for($headCoach, 'headCoach')->create([
        'name' => 'Tigers',
        'slug' => 'tigers-spring-2026',
    ]);

    Team::factory()->for($organization)->for($currentSeason)->for($division)->for($headCoach, 'headCoach')->create([
        'name' => 'Bears',
        'slug' => 'bears-spring-2026',
    ]);

    Context::add('organization', $organization);
    session()->put('current_season_id', $currentSeason->id);

    Livewire::actingAs($user)
        ->test('season.rollover')
        ->set('name', 'Fall 2026')
        ->call('rollover')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $newSeason = Season::query()
        ->whereBelongsTo($organization)
        ->where('name', 'Fall 2026')
        ->first();

    expect($newSeason)->not->toBeNull();
    expect($newSeason?->active)->toBeTrue();
    expect($newSeason?->start_date->toDateString())->toBe('2027-03-01');
    expect($newSeason?->end_date->toDateString())->toBe('2027-07-01');

    expect($currentSeason->fresh()->active)->toBeFalse();
    expect(session('current_season_id'))->toBe($newSeason?->id);

    $rolledTeams = Team::withoutGlobalScope('current_season')
        ->where('organization_id', $organization->id)
        ->where('season_id', $newSeason?->id)
        ->orderBy('name')
        ->get();

    expect($rolledTeams)->toHaveCount(2);
    expect($rolledTeams->pluck('name')->all())->toBe(['Bears', 'Tigers']);
    expect($rolledTeams->pluck('slug')->all())
        ->toBe(['bears-fall-2026', 'tigers-fall-2026']);
});
