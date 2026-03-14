<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isCustomer()) return $user->customer?->id === $booking->customer_id;
        if ($user->isStaff()) return $user->staffProfile?->id === $booking->assigned_staff_id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isStaff()) return $user->staffProfile?->id === $booking->assigned_staff_id;
        return false;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isCustomer()
            && $user->customer?->id === $booking->customer_id
            && $booking->isPending();
    }

    public function delete(User $user): bool { return $user->isSuperAdmin(); }

    public function assignStaff(User $user): bool { return $user->isAdmin(); }

    public function updateStatus(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) return true;
        return $user->isStaff() && $user->staffProfile?->id === $booking->assigned_staff_id;
    }
}
