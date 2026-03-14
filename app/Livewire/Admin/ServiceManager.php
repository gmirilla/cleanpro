<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Services')]
class ServiceManager extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $categoryFilter = '';
    public string $statusFilter  = '';
    public bool   $showModal     = false;
    public ?int   $editingId     = null;

    public string $name             = '';
    public string $category         = 'cleaning';
    public string $description      = '';
    public string $base_price       = '';
    public int    $duration_minutes = 60;
    public string $status           = 'active';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'category'         => 'required|in:cleaning,laundry',
            'description'      => 'nullable|string|max:1000',
            'base_price'       => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'status'           => 'required|in:active,inactive',
        ];
    }

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedCategoryFilter(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $service         = Service::findOrFail($id);
        $this->editingId = $id;
        $this->fill($service->only('name','category','description','base_price','duration_minutes','status'));
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            Service::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Service updated.');
        } else {
            Service::create($data);
            $this->dispatch('notify', type: 'success', message: 'Service created.');
        }
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        Service::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Service deleted.');
    }

    public function toggleStatus(int $id): void
    {
        $service = Service::findOrFail($id);
        $service->update(['status' => $service->status === 'active' ? 'inactive' : 'active']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('name','description','base_price');
        $this->category         = 'cleaning';
        $this->duration_minutes = 60;
        $this->status           = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $services = Service::query()
            ->when($this->search, fn($q) => $q->where('name','like',"%{$this->search}%"))
            ->when($this->categoryFilter, fn($q) => $q->where('category',$this->categoryFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status',$this->statusFilter))
            ->orderBy('category')->orderBy('sort_order')
            ->paginate(12);

        return view('livewire.admin.service-manager', compact('services'));
    }
}
