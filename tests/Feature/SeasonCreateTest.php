<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Livewire\Livewire;

test('first season form creates a season for the current organization', function (): void {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    Context::add('organization', $organization);

    Livewire::actingAs($user)
        ->test('season.create')
        ->set('name', 'Spring 2026')
        ->set('start_date', '2026-03-01')
        ->set('end_date', '2026-07-01')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $season = Season::query()
        ->whereBelongsTo($organization)
        ->where('name', 'Spring 2026')
        ->first();

    expect($season)->not->toBeNull();
    expect($season?->active)->toBeTrue();
});
