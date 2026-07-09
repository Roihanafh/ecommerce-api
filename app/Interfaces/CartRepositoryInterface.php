<?php

namespace App\Interfaces;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\User;

interface CartRepositoryInterface
{
    public function findActiveByUser(User $user): ?Cart;

    public function create(User $user): Cart;

    public function loadWithItems(Cart $cart): Cart;
}
