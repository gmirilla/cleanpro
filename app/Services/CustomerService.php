<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password'] ?? 'password'),
                'role'     => 'customer',
            ]);

            $customer = Customer::create([
                'user_id' => $user->id,
                'phone'   => $data['phone'] ?? null,
                'notes'   => $data['notes'] ?? null,
            ]);

            if (!empty($data['address'])) {
                Address::create([
                    'customer_id' => $customer->id,
                    'label'       => 'Home',
                    'address'     => $data['address'],
                    'city'        => $data['city'],
                    'state'       => $data['state'],
                    'is_default'  => true,
                ]);
            }

            return $customer;
        });
    }

    public function addAddress(Customer $customer, array $data): Address
    {
        if ($data['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        return Address::create(array_merge($data, ['customer_id' => $customer->id]));
    }

    public function setDefaultAddress(Customer $customer, int $addressId): void
    {
        DB::transaction(function () use ($customer, $addressId) {
            $customer->addresses()->update(['is_default' => false]);
            $customer->addresses()->where('id', $addressId)->update(['is_default' => true]);
        });
    }
}
