<x-layouts::app :title="__('Seasons')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        @if (\App\Models\Season::exists())
            <flux:card>
                <livewire:season.switcher />
            </flux:card>

            <flux:card>
                <livewire:season.rollover />
            </flux:card>
        @else
            <flux:card>
                <livewire:season.create />
            </flux:card>
        @endif
    </div>
</x-layouts::app>
