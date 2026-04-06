<x-layouts::app :title="__('Teams')">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Teams</flux:heading>
        @if($hasTeams)
            <flux:button wire:navigate :href="route('team.create')" size="sm" variant="primary">Add Team</flux:button>
        @endif
    </div>
    <flux:separator variant="subtle" class="my-3" />
    <div>
        @if($hasTeams)
            yes
        @else
            <div class="mx-auto w-full max-w-3xl">
                <flux:card>
                    <livewire:team.create />
                </flux:card>
            </div>
        @endif
    </div>
</x-layouts::app>
