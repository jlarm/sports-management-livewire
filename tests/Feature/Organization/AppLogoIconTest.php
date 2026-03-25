<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('app logo icon renders organization logo url when organization has a logo', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create([
        'logo_path' => 'organization-logos/team-logo.png',
    ]);

    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url('organization-logos/team-logo.png'), false);
});
