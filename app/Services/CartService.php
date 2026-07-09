<?php

namespace App\Services;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Interfaces\CartItemRepositoryInterface;
use App\Interfaces\CartRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected CartItemRepositoryInterface $cartItemRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function getOrCreateCart(): Cart
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->cartRepository->findActiveByUser($user)
            ?? $this->cartRepository->create($user);
    }

    public function index(): Cart
    {
        $cart = $this->getOrCreateCart();

        return $this->cartRepository->loadWithItems($cart);
    }

    public function store(AddToCartRequest $request): array
    {
        $product = $this->productRepository->findById((int) $request->input('product_id'));
        $quantity = (int) $request->input('quantity');

        // validasi stok
        if ($quantity > $product->stock) {
            return [
                'warning' => true,
                'message' => "Stok tidak cukup. Stok tersedia: {$product->stock}.",
            ];
        }

        $cart = $this->getOrCreateCart();
        $existing = $this->cartItemRepository->findByCartAndProduct($cart, $product->id);

        if ($existing) {
            $newQty = $existing->quantity + $quantity;

            if ($newQty > $product->stock) {
                return [
                    'warning' => true,
                    'message' => "Total quantity melebihi stok. Stok tersedia: {$product->stock}, sudah di cart: {$existing->quantity}.",
                ];
            }

            $this->cartItemRepository->updateQuantity($existing, $newQty);
        } else {
            $this->cartItemRepository->create($cart, $product->id, $quantity);
        }

        return ['cart' => $this->cartRepository->loadWithItems($cart)];
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

        $cartItem = $this->cartItemRepository->updateQuantity($cartItem, $quantity);
        $cartItem->load('product');

        return ['item' => $cartItem];
    }

    public function destroy(CartItem $cartItem): void
    {
        $this->cartItemRepository->delete($cartItem);
    }

    public function clear(): void
    {
        $cart = $this->getOrCreateCart();
        $this->cartItemRepository->deleteAllByCart($cart);
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
