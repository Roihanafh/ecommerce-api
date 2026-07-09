<?php

namespace App\Interfaces;

use App\Models\Cart;
use App\Models\CartItem;

interface CartItemRepositoryInterface
{
    public function findByCartAndProduct(Cart $cart, int $productId): ?CartItem;

    public function create(Cart $cart, int $productId, int $quantity): CartItem;

    public function updateQuantity(CartItem $cartItem, int $quantity): CartItem;

    public function delete(CartItem $cartItem): void;

    public function deleteAllByCart(Cart $cart): void;
}
