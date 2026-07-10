<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function createOrder(User $user, string $invoiceNumber, float $total): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'invoice_number' => $invoiceNumber,
            'total_amount' => $total,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }

    public function createOrderItem(Order $order, array $data): void
    {
        $order->items()->create($data);
    }

    public function getByUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->orders()
            ->with('items')
            ->latest()
            ->paginate($perPage);
    }

    public function findByIdAndUser(int $id, User $user): Order
    {
        return $user->orders()
            ->with('items')
            ->findOrFail($id);
    }
}
