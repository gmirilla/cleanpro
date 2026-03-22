<?php

namespace App\Livewire\Customer;

use App\Models\Address;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('My Profile')]
class CustomerProfile extends Component
{
    // ── Personal Info ────────────────────────────────────────────
    public string $name  = '';
    public string $email = '';
    public string $phone = '';
    public string $notes = '';

    // ── Password Change ──────────────────────────────────────────
    public string $current_password      = '';
    public string $new_password          = '';
    public string $new_password_confirm  = '';
    public bool   $showPasswordSection   = false;

    // ── Address Management ───────────────────────────────────────
    public bool   $showAddressModal  = false;
    public ?int   $editingAddressId  = null;
    public string $addr_label        = 'Home';
    public string $addr_address      = '';
    public string $addr_city         = '';
    public string $addr_state        = '';
    public string $addr_postal_code  = '';
    public bool   $addr_is_default   = false;

    // ── UI state ─────────────────────────────────────────────────
    public string $activeTab = 'profile'; // profile | addresses | security

    protected function profileRules(): array
    {
        $user = auth()->user();
        return [
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected function passwordRules(): array
    {
        return [
            'current_password'     => 'required|string|current_password',
            'new_password'         => 'required|string|min:8|confirmed:new_password_confirm',
            'new_password_confirm' => 'required|string',
        ];
    }

    protected function addressRules(): array
    {
        return [
            'addr_label'   => 'required|string|max:50',
            'addr_address' => 'required|string|max:255',
            'addr_city'    => 'required|string|max:100',
            'addr_state'   => 'required|string|max:100',
            'addr_postal_code' => 'nullable|string|max:20',
        ];
    }

    public function mount(): void
    {
        $user     = auth()->user();
        $customer = $user->customer;

        $this->name  = $user->name;
        $this->email = $user->email;
        $this->phone = $customer?->phone ?? '';
        $this->notes = $customer?->notes ?? '';
    }

    // ── Profile Save ─────────────────────────────────────────────

    public function saveProfile(): void
    {
        $this->validate($this->profileRules());

        $user = auth()->user();

        if ($user->email !== $this->email) {
            $user->email_verified_at = null;
        }

        $user->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $customer = $user->customer;
        if ($customer) {
            $customer->update([
                'phone' => $this->phone,
                'notes' => $this->notes,
            ]);
        }

        $this->dispatch('notify', type: 'success', message: 'Profile updated successfully.');
    }

    // ── Password Change ──────────────────────────────────────────

    public function savePassword(): void
    {
        $this->validate($this->passwordRules());

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset('current_password', 'new_password', 'new_password_confirm');
        $this->showPasswordSection = false;
        $this->dispatch('notify', type: 'success', message: 'Password updated successfully.');
    }

    // ── Address CRUD ─────────────────────────────────────────────

    public function openCreateAddress(): void
    {
        $this->resetAddressForm();
        $this->showAddressModal = true;
    }

    public function openEditAddress(int $id): void
    {
        $address = Address::where('customer_id', auth()->user()->customer->id)
            ->findOrFail($id);

        $this->editingAddressId  = $id;
        $this->addr_label        = $address->label;
        $this->addr_address      = $address->address;
        $this->addr_city         = $address->city;
        $this->addr_state        = $address->state;
        $this->addr_postal_code  = $address->postal_code ?? '';
        $this->addr_is_default   = $address->is_default;
        $this->showAddressModal  = true;
    }

    public function saveAddress(): void
    {
        $this->validate($this->addressRules());

        $customer = auth()->user()->customer;

        if ($this->addr_is_default) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $data = [
            'customer_id' => $customer->id,
            'label'       => $this->addr_label,
            'address'     => $this->addr_address,
            'city'        => $this->addr_city,
            'state'       => $this->addr_state,
            'postal_code' => $this->addr_postal_code ?: null,
            'is_default'  => $this->addr_is_default,
        ];

        if ($this->editingAddressId) {
            Address::where('customer_id', $customer->id)
                ->findOrFail($this->editingAddressId)
                ->update($data);
            $message = 'Address updated.';
        } else {
            // If this is the first address, make it default
            if ($customer->addresses()->count() === 0) {
                $data['is_default'] = true;
            }
            Address::create($data);
            $message = 'Address added.';
        }

        $this->closeAddressModal();
        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function setDefaultAddress(int $id): void
    {
        $customer = auth()->user()->customer;
        $customer->addresses()->update(['is_default' => false]);
        $customer->addresses()->where('id', $id)->update(['is_default' => true]);
        $this->dispatch('notify', type: 'success', message: 'Default address updated.');
    }

    public function deleteAddress(int $id): void
    {
        $customer = auth()->user()->customer;
        $address  = Address::where('customer_id', $customer->id)->findOrFail($id);

        $wasDefault = $address->is_default;
        $address->delete();

        // Promote the first remaining address to default
        if ($wasDefault) {
            $customer->addresses()->first()?->update(['is_default' => true]);
        }

        $this->dispatch('notify', type: 'success', message: 'Address deleted.');
    }

    public function closeAddressModal(): void
    {
        $this->showAddressModal = false;
        $this->editingAddressId = null;
        $this->resetAddressForm();
        $this->resetValidation();
    }

    private function resetAddressForm(): void
    {
        $this->addr_label       = 'Home';
        $this->addr_address     = '';
        $this->addr_city        = '';
        $this->addr_state       = '';
        $this->addr_postal_code = '';
        $this->addr_is_default  = false;
    }

    // ── Render ───────────────────────────────────────────────────

    public function render()
    {
        $customer  = auth()->user()->customer;
        $addresses = $customer ? $customer->addresses()->orderByDesc('is_default')->get() : collect();

        return view('livewire.customer.customer-profile', compact('addresses'));
    }
}
