<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('organization settings form stores uploaded logo and updates organization', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create([
        'logo_path' => null,
        'primary_color' => '#112233',
    ]);

    $user->organizations()->attach($organization, ['role' => Role::OWNER]);

    Context::add('organization', $organization);

    Livewire::actingAs($user)
        ->test('organization.settings-form')
        ->set('name', 'Updated Organization')
        ->set('color', '#445566')
        ->set('image', UploadedFile::fake()->image('logo.png'))
        ->call('update')
        ->assertHasNoErrors()
        ->assertDispatched('organization-theme-updated', color: '#445566');

    $organization->refresh();

    expect($organization->name)->toBe('Updated Organization');
    expect($organization->primary_color)->toBe('#445566');
    expect($organization->logo_path)->not->toBeNull();
    expect($organization->logo_path)->toStartWith('organization-logos/');

    Storage::disk('public')->assertExists($organization->logo_path);
});
