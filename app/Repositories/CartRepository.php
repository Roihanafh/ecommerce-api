<?php

namespace App\Repositories;

use App\Enums\CartStatus;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\User;

class CartRepository implements CartRepositoryInterface
{
    public function findActiveByUser(User $user): ?Cart
    {
        return $user->carts()
            ->where('status', CartStatus::Active)
            ->first();
    }

    public function create(User $user): Cart
    {
        return $user->carts()->create([
            'status' => CartStatus::Active,
        ]);
    }

    public function loadWithItems(Cart $cart): Cart
    {
        return $cart->load('items.product');
    }
}
