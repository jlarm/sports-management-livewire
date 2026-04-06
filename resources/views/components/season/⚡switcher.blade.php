<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Season;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?Organization $organization = null;

    public string $currentSeasonId = '';

    public function mount(): void
    {
        $this->organization = Context::get('organization');
        $this->currentSeasonId = (string) session('current_season_id', '');
    }

    #[Computed]
    public function seasons(): Collection
    {
        if (! $this->organization instanceof Organization) {
            return collect();
        }

        return Season::query()
            ->whereBelongsTo($this->organization)
            ->orderByDesc('active')
            ->orderByDesc('start_date')
            ->get();
    }

    public function updatedCurrentSeasonId(string $seasonId): void
    {
        if (! $this->organization instanceof Organization) {
            return;
        }

        $validated = Validator::make(
            ['season_id' => $seasonId],
            [
                'season_id' => [
                    'required',
                    'integer',
                    Rule::exists(Season::class, 'id')->where(
                        fn ($query) => $query->where('organization_id', $this->organization->id),
                    ),
                ],
            ],
        )->validate();

        session()->put('current_season_id', (int) $validated['season_id']);

        Flux::toast(
            text: 'Current season updated',
            variant: 'success',
        );
    }
};
?>

@if ($this->seasons->isNotEmpty())
    <div class="space-y-2">
        <flux:heading size="lg">Current season</flux:heading>
        <flux:field>
            <flux:label>Select season</flux:label>
            <flux:select wire:model.live="currentSeasonId">
                @foreach ($this->seasons as $season)
                    <option value="{{ $season->id }}">
                        {{ $season->name }}@if ($season->active) · Active @endif
                    </option>
                @endforeach
            </flux:select>
            <flux:text>Select which season team data should use across the app.</flux:text>
        </flux:field>
    </div>
@endif
