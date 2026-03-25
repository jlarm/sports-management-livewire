<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Context;

test('organization context is not set for guests', function () {
    $this->get(route('dashboard'));

    expect(Context::get('organization'))->toBeNull();
});

test('organization context is set for authenticated users with an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization);

    $this->actingAs($user)->get(route('dashboard'));

    expect(Context::get('organization'))
        ->toBeInstanceOf(Organization::class)
        ->id->toBe($organization->id);
});

test('organization context is null when authenticated user has no organization', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'));

    expect(Context::get('organization'))->toBeNull();
});
