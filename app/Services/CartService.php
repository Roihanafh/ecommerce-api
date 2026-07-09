<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getOrCreateCart(): Cart
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->carts()
            ->where('status', CartStatus::Active)
            ->firstOrCreate(
                ['user_id' => $user->id, 'status' => CartStatus::Active->value],
            );
    }

    public function index(): Cart
    {
        $cart = $this->getOrCreateCart();
        $cart->load('items.product');

        return $cart;
    }

    public function store(AddToCartRequest $request): array
    {
        $product = Product::findOrFail($request->input('product_id'));
        $quantity = (int) $request->input('quantity');

        // validasi stok
        if ($quantity > $product->stock) {
            return [
                'warning' => true,
                'message' => "Stok tidak cukup. Stok tersedia: {$product->stock}.",
            ];
        }

        $cart = $this->getOrCreateCart();
        $existing = $cart->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $newQty = $existing->quantity + $quantity;

            if ($newQty > $product->stock) {
                return [
                    'warning' => true,
                    'message' => "Total quantity melebihi stok. Stok tersedia: {$product->stock}, sudah di cart: {$existing->quantity}.",
                ];
            }

            $existing->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        $cart->load('items.product');

        return ['cart' => $cart];
    }

    public function update(UpdateCartRequest $request, CartItem $cartItem): array
    {
        $quantity = (int) $request->input('quantity');
        $product = $cartItem->product;

        if ($quantity > $product->stock) {
            return [
                'warning' => true,
                'message' => "Stok tidak cukup. Stok tersedia: {$product->stock}.",
            ];
        }

        $cartItem->update(['quantity' => $quantity]);
        $cartItem->load('product');

        return ['item' => $cartItem];
    }

    public function destroy(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function clear(): void
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
    }

    public function calculateTotal(Cart $cart): float
    {
        $total = 0.0;

        foreach ($cart->items as $item) {
            $total += $item->quantity * ($item->product->price ?? 0);
        }

        return $total;
    }
}
