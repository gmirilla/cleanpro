<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) return true;
        return $user->customer?->id === $payment->booking->customer_id;
    }
    public function create(User $user): bool { return true; }
    public function refund(User $user): bool { return $user->isSuperAdmin(); }
}
