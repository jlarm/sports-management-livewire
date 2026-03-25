<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

test('dashboard shows create organization form when user has no organization', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSeeLivewire('organization.create');
});

test('dashboard shows normal content when user has an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertDontSeeLivewire('organization.create');
});

test('user can create an organization', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('organization.create')
        ->set('name', 'City Youth Soccer')
        ->call('create')
        ->assertRedirect(route('dashboard'));

    $organization = Organization::first();

    expect($organization)
        ->name->toBe('City Youth Soccer')
        ->owner_id->toBe($user->id);

    expect($user->organizations()->where('organization_id', $organization->id)->exists())->toBeTrue();

    expect($user->organizations()->first()->pivot->role)->toBe(Role::OWNER->value);
});

test('organization name is required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('organization.create')
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);
});

test('organization name cannot exceed 255 characters', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('organization.create')
        ->set('name', str_repeat('a', 256))
        ->call('create')
        ->assertHasErrors(['name' => 'max']);
});
