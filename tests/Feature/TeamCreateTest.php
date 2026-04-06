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

test('team create normalizes age-based division input before creating it', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    Context::add('organization', $organization);

    Livewire::actingAs($user)
        ->test('team.create')
        ->set('search', '14')
        ->call('createDivision')
        ->assertHasNoErrors();

    $division = Division::query()
        ->whereBelongsTo($organization)
        ->first();

    expect($division)->not->toBeNull();
    expect($division?->name)->toBe('14U');
});

test('team create rejects non age-based division names', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    Context::add('organization', $organization);

    Livewire::actingAs($user)
        ->test('team.create')
        ->set('search', 'Varsity')
        ->call('createDivision')
        ->assertHasErrors(['search']);

    expect(Division::query()->whereBelongsTo($organization)->exists())->toBeFalse();
});

test('team create stores a team in the current season', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);
    $season = Season::factory()->for($organization)->create(['active' => true]);
    $division = Division::factory()->for($organization)->create([
        'name' => '12U',
    ]);

    Context::add('organization', $organization);
    session()->put('current_season_id', $season->id);

    Livewire::actingAs($user)
        ->test('team.create')
        ->set('name', 'Tigers')
        ->set('divisionId', (string) $division->id)
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('team.index'));

    $team = Team::withoutGlobalScopes()
        ->where('organization_id', $organization->id)
        ->where('season_id', $season->id)
        ->first();

    expect($team)->not->toBeNull();
    expect($team?->name)->toBe('Tigers');
    expect($team?->division_id)->toBe($division->id);
    expect($team?->slug)->toBe('tigers');
});
