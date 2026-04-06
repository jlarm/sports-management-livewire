<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Division;
use App\Models\Organization;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;

test('team index shows the team create form when there are no teams', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);
    Season::factory()->for($organization)->create(['active' => true]);

    $this->actingAs($user)
        ->get(route('team.index'))
        ->assertOk()
        ->assertSee('Create your first team');
});

test('team index shows the add button when teams exist', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);
    $season = Season::factory()->for($organization)->create(['active' => true]);
    $division = Division::factory()->for($organization)->create();

    $this->withSession(['current_season_id' => $season->id]);

    Team::factory()->for($organization)->for($season)->for($division)->create();

    $this->actingAs($user)
        ->get(route('team.index'))
        ->assertOk()
        ->assertSee('Add Team')
        ->assertDontSee('Create your first team');
});
