<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isKasir();
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->isOwner() || $sale->isOwnedBy($user);
    }

    public function printReceipt(User $user, Sale $sale): bool
    {
        return $this->view($user, $sale);
    }
}
