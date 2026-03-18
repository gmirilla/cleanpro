<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isAdmin()) return true;
        $booking = $invoice->booking ?? $invoice->booking()->withTrashed()->first();
        if (!$booking) return false;
        return $user->customer?->id === $booking->customer_id;
    }
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }
    public function delete(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
