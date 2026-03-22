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
    public bool   $showRegisterModal = false;
    public ?int   $viewingId = null;

    // ── Create (admin-only, no password) ─────────────────────────
    public string $name='', $email='', $phone='', $address='', $city='', $state='', $notes='';

    // ── Register (full self-service customer) ────────────────────
    public string $reg_name='', $reg_email='', $reg_phone='';
    public string $reg_password='', $reg_password_confirm='';
    public string $reg_address='', $reg_city='', $reg_state='';
    public bool   $reg_send_welcome = true;

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

    protected function registerRules(): array
    {
        return [
            'reg_name'             => 'required|string|max:255',
            'reg_email'            => 'required|email|unique:users,email',
            'reg_phone'            => 'nullable|string|max:20',
            'reg_password'         => 'required|string|min:8|same:reg_password_confirm',
            'reg_password_confirm' => 'required|string',
            'reg_address'          => 'nullable|string|max:255',
            'reg_city'             => 'nullable|string|max:100',
            'reg_state'            => 'nullable|string|max:100',
        ];
    }

    protected array $registerMessages = [
        'reg_name.required'             => 'Full name is required.',
        'reg_email.required'            => 'Email address is required.',
        'reg_email.unique'              => 'This email is already registered.',
        'reg_password.required'         => 'Password is required.',
        'reg_password.min'              => 'Password must be at least 8 characters.',
        'reg_password.same'             => 'Passwords do not match.',
        'reg_password_confirm.required' => 'Please confirm the password.',
    ];

    public function updatedSearch(): void { $this->resetPage(); }

    // ── Admin Quick-Create (no password set by admin) ─────────────

    public function create(CustomerService $customerService): void
    {
        $customerService->create($this->validate());
        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: 'Customer created. Default password is "password".');
    }

    // ── Full Registration (with password) ─────────────────────────

    public function openRegisterModal(): void
    {
        $this->resetRegisterForm();
        $this->showRegisterModal = true;
    }

    public function register(CustomerService $customerService): void
    {
        $this->validate(
            $this->registerRules(),
            $this->registerMessages
        );

        $customerService->create([
            'name'     => $this->reg_name,
            'email'    => $this->reg_email,
            'password' => $this->reg_password,
            'phone'    => $this->reg_phone,
            'address'  => $this->reg_address,
            'city'     => $this->reg_city,
            'state'    => $this->reg_state,
        ]);

        $this->closeRegisterModal();
        $this->dispatch('notify', type: 'success', message: "Customer \"{$this->reg_name}\" registered successfully.");
    }

    public function closeRegisterModal(): void
    {
        $this->showRegisterModal = false;
        $this->resetRegisterForm();
        $this->resetValidation();
    }

    // ── View / Detail ─────────────────────────────────────────────

    public function view(int $id): void { $this->viewingId = $id; }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewingId = null;
        $this->reset('name','email','phone','address','city','state','notes');
        $this->resetValidation();
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function resetRegisterForm(): void
    {
        $this->reg_name             = '';
        $this->reg_email            = '';
        $this->reg_phone            = '';
        $this->reg_password         = '';
        $this->reg_password_confirm = '';
        $this->reg_address          = '';
        $this->reg_city             = '';
        $this->reg_state            = '';
        $this->reg_send_welcome     = true;
    }

    // ── Render ────────────────────────────────────────────────────

    public function render(CustomerRepository $repo)
    {
        $customers = $repo->paginate(15, ['search' => $this->search]);
        $viewingCustomer = $this->viewingId
            ? Customer::with(['user','addresses','bookings.items.service'])->find($this->viewingId)
            : null;

        return view('livewire.admin.customer-manager', compact('customers', 'viewingCustomer'));
    }
}
