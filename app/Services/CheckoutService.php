<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Interfaces\CartRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Main checkout flow — wrapped in a DB transaction.
     * Throws ValidationException if cart is empty or stock is insufficient.
     */
    public function checkout(): Order
    {
        /** @var User $user */
        $user = Auth::user();

        return DB::transaction(function () use ($user) {
            $cart = $this->cartRepository->findActiveByUser($user);

            if (! $cart || $cart->items->isEmpty()) {
                // Eager-load items jika belum
                $cart?->load('items.product');

                if (! $cart || $cart->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Cart kosong. Tambahkan produk sebelum checkout.'],
                    ]);
                }
            }

            $this->cartRepository->loadWithItems($cart);

            $this->validateStock($cart);

            $total = $this->calculateTotal($cart);
            $invoiceNumber = $this->generateInvoice();

            $order = $this->createOrder($user, $invoiceNumber, $total);

            $this->createOrderItems($order, $cart);

            $this->decreaseStock($cart);

            $this->clearCart($cart);

            return $order->load('items');
        });
    }

    /**
     * Generate unique invoice number: INV-YYYYMMDD-XXXXX
     */
    public function generateInvoice(): string
    {
        $date = now()->format('Ymd');
        $suffix = strtoupper(substr(uniqid(), -5));

        return "INV-{$date}-{$suffix}";
    }

    /**
     * Validate stock for all items in the cart.
     * Throws ValidationException on first insufficient item.
     */
    public function validateStock(Cart $cart): void
    {
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                throw ValidationException::withMessages([
                    'stock' => [
                        "Stok produk '{$item->product->name}' tidak cukup. "
                        ."Diminta: {$item->quantity}, tersedia: {$item->product->stock}.",
                    ],
                ]);
            }
        }
    }

    /**
     * Calculate total price of all items in the cart.
     */
    public function calculateTotal(Cart $cart): float
    {
        return (float) $cart->items->sum(
            fn ($item) => $item->quantity * $item->product->price
        );
    }

    /**
     * Create the order record.
     */
    public function createOrder(User $user, string $invoiceNumber, float $total): Order
    {
        return $this->orderRepository->createOrder($user, $invoiceNumber, $total);
    }

    /**
     * Snapshot each cart item into order_items (price at time of purchase).
     */
    public function createOrderItems(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {
            $this->orderRepository->createOrderItem($order, [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'subtotal' => $item->quantity * $item->product->price,
            ]);
        }
    }

    /**
     * Decrease product stock for each item purchased.
     */
    public function decreaseStock(Cart $cart): void
    {
        foreach ($cart->items as $item) {
            $this->productRepository->decreaseStock($item->product, $item->quantity);
        }
    }

    /**
     * Mark cart as checked_out (not delete, for order history traceability).
     */
    public function clearCart(Cart $cart): void
    {
        $cart->update(['status' => CartStatus::CheckedOut]);
    }

    /**
     * Paginated order history for the authenticated user.
     */
    public function history(): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->orderRepository->getByUser($user);
    }

    /**
     * Get a single order belonging to the authenticated user.
     */
    public function show(int $id): Order
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->orderRepository->findByIdAndUser($id, $user);
    }
}
