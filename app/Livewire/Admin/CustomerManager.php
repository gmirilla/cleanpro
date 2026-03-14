<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Customers')]
class CustomerManager extends Component
{
    use WithPagination;

    public string $search    = '';
    public bool   $showModal = false;
    public ?int   $viewingId = null;

    public string $name='', $email='', $phone='', $address='', $city='', $state='', $notes='';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'notes'   => 'nullable|string|max:500',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function create(CustomerService $customerService): void
    {
        $customerService->create($this->validate());
        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: 'Customer created.');
    }

    public function view(int $id): void { $this->viewingId = $id; }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewingId = null;
        $this->reset('name','email','phone','address','city','state','notes');
        $this->resetValidation();
    }

    public function render(CustomerRepository $repo)
    {
        $customers = $repo->paginate(15, ['search' => $this->search]);
        $viewingCustomer = $this->viewingId
            ? Customer::with(['user','addresses','bookings.items.service'])->find($this->viewingId)
            : null;

        return view('livewire.admin.customer-manager', compact('customers', 'viewingCustomer'));
    }
}
