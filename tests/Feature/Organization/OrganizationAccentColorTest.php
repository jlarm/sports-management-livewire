<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;

test('organization custom color is exposed as the app accent css variable', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create([
        'primary_color' => '#123456',
    ]);

    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('--color-accent: #123456;', false)
        ->assertSee('--color-accent-content: #123456;', false);
});
