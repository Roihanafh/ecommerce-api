<?php

namespace App\Interfaces;

use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function createOrder(User $user, string $invoiceNumber, float $total): Order;

    public function createOrderItem(Order $order, array $data): void;

    public function getByUser(User $user, int $perPage = 10): LengthAwarePaginator;

    public function findByIdAndUser(int $id, User $user): Order;
}
