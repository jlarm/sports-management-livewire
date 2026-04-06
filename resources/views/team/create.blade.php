<x-layouts::app :title="__('Create Team')">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Create Team</flux:heading>
        <flux:button wire:navigate :href="route('team.index')" size="sm" icon="arrow-left">Back</flux:button>
    </div>
    <flux:separator variant="subtle" class="my-3" />
    <div class="w-full max-w-3xl mx-auto">
        <flux:card>
            <livewire:team.create />
        </flux:card>
    </div>
</x-layouts::app>
