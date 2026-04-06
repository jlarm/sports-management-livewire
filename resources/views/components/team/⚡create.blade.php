<?php

declare(strict_types=1);

use App\Models\Division;
use App\Models\Organization;
use App\Models\Season;
use App\Models\Team;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    public ?Organization $organization = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|integer')]
    public string $divisionId = '';
    public string $search = '';

    public function mount(): void
    {
        $this->organization = Context::get('organization');
    }

    #[Computed]
    public function divisions(): Collection
    {
        if (! $this->organization instanceof Organization) {
            return collect();
        }

        return Division::query()
            ->whereBelongsTo($this->organization)
            ->when(
                $this->search !== '',
                fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'),
            )
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function currentSeason(): ?Season
    {
        if (! $this->organization instanceof Organization) {
            return null;
        }

        $currentSeasonId = session('current_season_id');

        if (! is_numeric($currentSeasonId)) {
            return null;
        }

        return Season::query()
            ->whereBelongsTo($this->organization)
            ->find((int) $currentSeasonId);
    }

    public function createDivision(): void
    {
        if (! $this->organization instanceof Organization) {
            return;
        }

        $this->resetValidation('search');

        if (preg_match('/^\d+\s*u?$/i', $this->search) !== 1) {
            $this->addError('search', 'Division must be an age group like 12U.');

            return;
        }

        $division = Division::query()->firstOrCreate(
            [
                'organization_id' => $this->organization->id,
                'name' => Division::canonicalName($this->search),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'display_order' => ((int) Division::query()
                    ->whereBelongsTo($this->organization)
                    ->max('display_order')) + 1,
            ],
        );

        $this->divisionId = (string) $division->id;
        $this->search = $division->name;

        Flux::toast(
            text: 'Division ready to use',
            variant: 'success',
        );
    }

    public function create(): void
    {
        if (! $this->organization instanceof Organization) {
            return;
        }

        $currentSeason = $this->currentSeason;

        if (! $currentSeason instanceof Season) {
            $this->addError('divisionId', 'Create or select a current season before adding a team.');

            return;
        }

        $validated = $this->validate();

        $division = Division::query()
            ->whereBelongsTo($this->organization)
            ->find($validated['divisionId']);

        if (! $division instanceof Division) {
            $this->addError('divisionId', 'Select a valid division.');

            return;
        }

        Team::create([
            'organization_id' => $this->organization->id,
            'season_id' => $currentSeason->id,
            'division_id' => $division->id,
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'head_coach_id' => null,
        ]);

        $this->reset(['name', 'divisionId', 'search']);

        Flux::toast(
            text: 'Team created successfully',
            variant: 'success',
        );

        $this->redirect(route('team.index'), navigate: true);
    }

    private function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'team';
        $candidate = $baseSlug;
        $suffix = 2;

        while (Team::withoutGlobalScopes()->where('slug', $candidate)->exists()) {
            $candidate = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
};
?>

<div>
    <flux:heading size="xl">Create your first team</flux:heading>
    <flux:text class="mb-6">Set up a team to start managing coaches and players.</flux:text>
    <form wire:submit.prevent="create" class="space-y-4 pt-4">
        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Division</flux:label>
            <flux:select wire:model="divisionId" variant="combobox">
                <x-slot name="input">
                    <flux:select.input wire:model.live="search" placeholder="Type 12U or 14" />
                </x-slot>

                @foreach ($this->divisions as $division)
                    <flux:select.option :value="$division->id">{{ $division->name }}</flux:select.option>
                @endforeach

                <flux:select.option.create wire:click="createDivision" min-length="1">
                    Create "<span wire:text="search"></span>"
                </flux:select.option.create>
            </flux:select>
            <flux:text>Only age-style divisions are allowed. Examples: 12U, 14U, 15U.</flux:text>
            <flux:error name="divisionId" />
            <flux:error name="search" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            Create team
        </flux:button>
    </form>
</div>
