<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\Season;
use App\Models\User;

test('season index shows the create form when the organization has no seasons', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $this->actingAs($user)
        ->get(route('season.index'))
        ->assertOk()
        ->assertSee('Create your first season');
});

test('season index shows the season management tools when the organization has seasons', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    Season::factory()->for($organization)->create([
        'name' => 'Spring 2026',
        'active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('season.index'))
        ->assertOk()
        ->assertSee('Current season')
        ->assertSee('Season rollover');
});
