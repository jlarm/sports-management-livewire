<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;

test('user without an organization is redirected to dashboard when accessing org routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('organization.settings'))
        ->assertRedirect(route('dashboard'));
});

test('user with an organization can access org routes', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $this->actingAs($user)
        ->get(route('organization.settings'))
        ->assertSuccessful();
});
