<?php

declare(strict_types=1);

use App\Models\Organization;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Flux\Flux;

new class extends Component {
    use WithFileUploads;

    public ?Organization $organization = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|image|max:10240')]
    public ?TemporaryUploadedFile $image = null;

    #[Validate('nullable|string|regex:/^#[A-Fa-f0-9]{6}$/')]
    public string $color = '';

    public ?string $existingImagePath = null;

    public function mount(): void
    {
        $this->organization = Context::get('organization');

        if (! $this->organization instanceof Organization) {
            return;
        }

        $this->name = $this->organization->name ?? '';
        $this->existingImagePath = $this->organization->logo_path;
        $this->color = $this->organization->primary_color ?? '';
    }

    public function update(): void
    {
        if (! $this->organization instanceof Organization) {
            return;
        }

        $this->authorize('update', $this->organization);
        $this->validate();

        $logoPath = $this->existingImagePath;

        if ($this->image instanceof TemporaryUploadedFile) {
            $logoPath = $this->image->store('organization-logos', 'public');

            if (
                is_string($this->existingImagePath)
                && Storage::disk('public')->exists($this->existingImagePath)
            ) {
                Storage::disk('public')->delete($this->existingImagePath);
            }
        }

        $this->organization->update([
            'name' => $this->name,
            'logo_path' => $logoPath,
            'primary_color' => $this->color === '' ? null : $this->color,
        ]);

        $this->dispatch('organization-theme-updated', color: $this->color === '' ? null : $this->color);

        $this->existingImagePath = $logoPath;
        $this->image = null;

        Flux::toast(
            text: 'Organization updated successfully',
            variant: 'success'
        );
    }
};
?>

<form class="space-y-6" wire:submit="update">
    <flux:field>
        <flux:label>Organization Name</flux:label>
        <flux:input wire:model="name" />
        <flux:error name="name" />
    </flux:field>

    <flux:field>
        <flux:file-upload wire:model="image" label="Upload logo">
            <flux:file-upload.dropzone
                heading="Drop files here or click to browse"
                text="JPG, PNG, GIF up to 10MB"
            />
        </flux:file-upload>

        @if ($image instanceof TemporaryUploadedFile)
            <div class="mt-4 flex flex-col gap-2">
                <flux:file-item
                    :heading="$image->getClientOriginalName()"
                    :image="$image->temporaryUrl()"
                    :size="$image->getSize()"
                >
                    <x-slot name="actions">
                        <flux:file-item.remove wire:click="$set('image', null)" />
                    </x-slot>
                </flux:file-item>
            </div>
        @elseif ($existingImagePath)
            <div class="mt-4 flex flex-col gap-2">
                <flux:file-item
                    :heading="basename($existingImagePath)"
                    :image="Storage::disk('public')->url($existingImagePath)"
                >
                    <x-slot name="actions">
                        <flux:file-item.remove wire:click="$set('existingImagePath', null)" />
                    </x-slot>
                </flux:file-item>
            </div>
        @endif

        <flux:error name="image" />
    </flux:field>

    <flux:field>
        <flux:input type="color" wire:model="color" label="Color" />
        <flux:error name="color" />
    </flux:field>

    <flux:button type="submit" variant="primary">Update</flux:button>
</form>
