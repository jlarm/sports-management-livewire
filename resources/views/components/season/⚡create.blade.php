<?php

declare(strict_types=1);

use App\Models\Season;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|date')]
    public string $start_date = '';

    #[Validate('required|date|after:start_date')]
    public string $end_date = '';

    public function create(): void
    {
        $this->validate();

        Season::create([
            'organization_id' => context('organization')->id,
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'active' => 1,
        ]);

        Flux::toast(
            text: 'Season created successfully',
            variant: 'success',
        );

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="flex h-full w-full flex-1 items-center justify-center">
    <div class="w-full max-w-sm space-y-1">
        <flux:heading size="xl">Create your first season</flux:heading>
        <flux:text class="mb-6">Set up a season to start managing registrations and teams.</flux:text>

        <form wire:submit.prevent="create" class="space-y-4 pt-4">
            <flux:field>
                <flux:label>Season name</flux:label>
                <flux:input wire:model="name" placeholder="e.g. Spring {{ now()->format('Y') }}" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Start date</flux:label>
                <flux:date-picker wire:model="start_date" />
                <flux:error name="start_date" />
            </flux:field>

            <flux:field>
                <flux:label>End date</flux:label>
                <flux:date-picker wire:model="end_date" />
                <flux:error name="end_date" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                Create season
            </flux:button>
        </form>
    </div>
</div>
