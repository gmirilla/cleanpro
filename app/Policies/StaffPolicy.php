<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Staff $staff): bool { return $user->isAdmin() || $user->staffProfile?->id === $staff->id; }
    public function create(User $user): bool  { return $user->isAdmin(); }
    public function update(User $user, Staff $staff): bool { return $user->isAdmin(); }
    public function delete(User $user, Staff $staff): bool { return $user->isSuperAdmin(); }
}
