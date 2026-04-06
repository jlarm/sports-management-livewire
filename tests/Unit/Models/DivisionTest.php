<?php

declare(strict_types=1);

use App\Models\Division;
use App\Models\Organization;
use App\Models\Team;

test('to array', function (): void {
    $division = Division::factory()->create()->refresh();

    expect(array_keys($division->toArray()))
        ->toBe([
            'id',
            'uuid',
            'organization_id',
            'name',
            'display_order',
            'created_at',
            'updated_at',
        ]);
});

test('belongs to organization', function (): void {
    $organization = Organization::factory()->create();
    $division = Division::factory()->for($organization)->create();

    expect($division->organization->is($organization))->toBeTrue();
});

test('has many teams', function (): void {
    $division = Division::factory()->create();
    Team::factory()->count(3)->for($division)->create();

    expect($division->teams)
        ->toHaveCount(3)
        ->each(fn ($team) => $team->division_id->toBe($division->id));
});

test('normalizes age-based division names to a consistent format', function (): void {
    $division = Division::factory()->create([
        'name' => ' 14 u ',
    ])->refresh();

    expect($division->name)->toBe('14U');

    $division->update([
        'name' => '15',
    ]);

    expect($division->fresh()->name)->toBe('15U');
});

test('preserves non-age division names while trimming extra whitespace', function (): void {
    $division = Division::factory()->create([
        'name' => '  Varsity   Gold  ',
    ])->refresh();

    expect($division->name)->toBe('Varsity Gold');
});
