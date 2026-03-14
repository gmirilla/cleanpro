<?php

namespace App\Policies;

use App\Models\LaundryOrder;
use App\Models\User;

class LaundryOrderPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin() || $user->isStaff(); }
    public function view(User $user, LaundryOrder $order): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isStaff()) return $user->staffProfile?->id === $order->booking->assigned_staff_id;
        return $user->customer?->id === $order->booking->customer_id;
    }
    public function update(User $user, LaundryOrder $order): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isStaff() && $user->staffProfile?->id === $order->booking->assigned_staff_id;
    }
}
