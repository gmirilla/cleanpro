<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StaffService
{
    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password'] ?? 'password'),
                'role'     => 'staff',
            ]);

            return Staff::create([
                'user_id'             => $user->id,
                'phone'               => $data['phone'] ?? null,
                'position'            => $data['position'],
                'availability_status' => 'available',
                'working_days'        => $data['working_days'] ?? null,
                'shift_start'         => $data['shift_start'] ?? null,
                'shift_end'           => $data['shift_end'] ?? null,
            ]);
        });
    }

    public function update(Staff $staff, array $data): void
    {
        DB::transaction(function () use ($staff, $data) {
            $staff->update(array_intersect_key($data, array_flip([
                'phone', 'position', 'availability_status', 'working_days', 'shift_start', 'shift_end',
            ])));

            if (isset($data['name'])) {
                $staff->user->update(['name' => $data['name']]);
            }
        });
    }

    public function delete(Staff $staff): void
    {
        DB::transaction(function () use ($staff) {
            $staff->delete();
            $staff->user->delete();
        });
    }
}
