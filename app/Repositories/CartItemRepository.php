<?php

namespace App\Repositories;

use App\Interfaces\CartItemRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function findByCartAndProduct(Cart $cart, int $productId): ?CartItem
    {
        return $cart->items()
            ->where('product_id', $productId)
            ->first();
    }

    public function create(Cart $cart, int $productId, int $quantity): CartItem
    {
        return $cart->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem;
    }

    public function delete(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function deleteAllByCart(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
