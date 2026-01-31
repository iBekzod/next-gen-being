<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProductPurchase;

class ProductPurchasePolicy
{
    /**
     * Determine if the user can view the purchase
     */
    public function view(User $user, ProductPurchase $purchase): bool
    {
        return $user->id === $purchase->user_id;
    }

    /**
     * Determine if the user can download the purchase
     */
    public function download(User $user, ProductPurchase $purchase): bool
    {
        return $user->id === $purchase->user_id && $purchase->status === 'completed';
    }
}
