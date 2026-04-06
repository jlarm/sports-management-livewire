<?php

declare(strict_types=1);

use App\Actions\RolloverSeason;
use App\Models\Organization;
use App\Models\Season;
use Flux\Flux;
use Illuminate\Support\Facades\Context;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    protected RolloverSeason $rolloverSeason;

    public ?Organization $organization = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function boot(RolloverSeason $rolloverSeason): void
    {
        $this->rolloverSeason = $rolloverSeason;
    }

    public function mount(): void
    {
        $this->organization = Context::get('organization');
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

    public function rollover(): void
    {
        if (! $this->organization instanceof Organization) {
            return;
        }

        $currentSeason = $this->currentSeason;

        if (! $currentSeason instanceof Season) {
            return;
        }

        $this->authorize('update', $this->organization);
        $this->validate();

        $newSeason = $this->rolloverSeason->execute($currentSeason, $this->name);

        session()->put('current_season_id', $newSeason->id);

        Flux::toast(
            text: 'Season rolled over successfully',
            variant: 'success',
        );

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">Season rollover</flux:heading>
        @if ($this->currentSeason)
            <flux:text>
                Duplicate teams from {{ $this->currentSeason->name }} into a new season. Team rosters are not copied.
            </flux:text>
            <flux:text>
                Start and end dates roll forward one year from the selected season.
            </flux:text>
        @else
            <flux:text>Create an active season before running a rollover.</flux:text>
        @endif
    </div>

    <form wire:submit="rollover" class="space-y-4">
        <flux:field>
            <flux:label>New season name</flux:label>
            <flux:input
                wire:model="name"
                placeholder="e.g. Fall {{ now()->addYear()->format('Y') }}"
                :disabled="! $this->currentSeason"
            />
            <flux:error name="name" />
        </flux:field>

        <flux:button
            type="submit"
            variant="primary"
            wire:loading.attr="disabled"
            :disabled="! $this->currentSeason"
        >
            Roll over season
        </flux:button>
    </form>
</div>
