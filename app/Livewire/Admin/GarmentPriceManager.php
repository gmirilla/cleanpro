<?php

namespace App\Livewire\Admin;

use App\Models\GarmentPrice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Garment Prices')]
class GarmentPriceManager extends Component
{
    public bool   $showModal  = false;
    public ?int   $editingId  = null;

    // Form fields
    public string $label        = '';
    public string $garment_type = '';
    public string $price        = '';
    public bool   $is_active    = true;

    protected function rules(): array
    {
        $uniqueRule = $this->editingId
            ? 'unique:garment_prices,garment_type,' . $this->editingId
            : 'unique:garment_prices,garment_type';

        return [
            'label'        => 'required|string|max:100',
            'garment_type' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', $uniqueRule],
            'price'        => 'required|numeric|min:0',
            'is_active'    => 'boolean',
        ];
    }

    protected array $messages = [
        'garment_type.regex' => 'The slug may only contain lowercase letters, numbers, and underscores.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $garment         = GarmentPrice::findOrFail($id);
        $this->editingId = $id;
        $this->label        = $garment->label;
        $this->garment_type = $garment->garment_type;
        $this->price        = (string) $garment->price;
        $this->is_active    = $garment->is_active;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            GarmentPrice::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Garment type updated successfully.');
        } else {
            GarmentPrice::create($data);
            $this->dispatch('notify', type: 'success', message: 'Garment type created successfully.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $garment = GarmentPrice::findOrFail($id);
        $garment->update(['is_active' => ! $garment->is_active]);
        $this->dispatch('notify', type: 'info', message: 'Status updated.');
    }

    public function delete(int $id): void
    {
        GarmentPrice::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Garment type deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetForm();
    }

    // Auto-generate slug from label while typing (only on create)
    public function updatedLabel(string $value): void
    {
        if (! $this->editingId) {
            $this->garment_type = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($value)));
        }
    }

    private function resetForm(): void
    {
        $this->reset('label', 'garment_type', 'price');
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $garments = GarmentPrice::orderBy('label')->get();

        return view('livewire.admin.garment-price-manager', compact('garments'));
    }
}
