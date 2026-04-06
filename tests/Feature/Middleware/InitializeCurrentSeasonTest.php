<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\Season;
use App\Models\User;

test('active season is stored in the session for authenticated users', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $selectedSeason = Season::factory()->for($organization)->create([
        'active' => true,
    ]);

    Season::factory()->for($organization)->create([
        'active' => false,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    expect(session('current_season_id'))->toBe($selectedSeason->id);
});

test('existing season selection is preserved when it belongs to the organization', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $activeSeason = Season::factory()->for($organization)->create([
        'active' => true,
    ]);

    $selectedSeason = Season::factory()->for($organization)->create([
        'active' => false,
    ]);

    $this->actingAs($user)
        ->withSession(['current_season_id' => $selectedSeason->id])
        ->get(route('dashboard'))
        ->assertOk();

    expect(session('current_season_id'))
        ->toBe($selectedSeason->id)
        ->not->toBe($activeSeason->id);
});
