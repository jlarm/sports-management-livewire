<?php

use App\Enums\Role;
use App\Models\Organization;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public function create(): void
    {
        $this->validate();

        $organization = Organization::create([
            'name' => $this->name,
            'owner_id' => auth()->id(),
        ]);

        auth()->user()->organizations()->attach($organization, ['role' => Role::OWNER]);

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="flex h-full w-full flex-1 items-center justify-center">
    <div class="w-full max-w-sm space-y-1">
        <flux:heading size="xl">Create your organization</flux:heading>
        <flux:text class="mb-6">Set up your organization to get started managing your teams.</flux:text>

        <form wire:submit="create" class="space-y-4 pt-4">
            <flux:field>
                <flux:label>Organization name</flux:label>
                <flux:input wire:model="name" placeholder="e.g. City Youth Baseball" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                Get started
            </flux:button>
        </form>
    </div>
</div>
