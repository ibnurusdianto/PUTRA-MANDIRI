<?php

namespace App\Policies;

use App\Models\DigitalServiceDetail;
use App\Models\User;

class DigitalServiceDetailPolicy
{
    public function view(User $user, DigitalServiceDetail $detail): bool
    {
        $detail->loadMissing('saleItem.sale');

        return $user->isOwner() || (bool) $detail->saleItem?->sale?->isOwnedBy($user);
    }

    public function update(User $user, DigitalServiceDetail $detail): bool
    {
        return $this->view($user, $detail);
    }
}
