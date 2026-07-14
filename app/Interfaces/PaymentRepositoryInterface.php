<?php

namespace App\Interfaces;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function create(Order $order, string $snapToken, string $redirectUrl, string $idempotencyKey): Payment;

    public function findByIdempotencyKey(string $key): ?Payment;

    public function findByTransactionId(string $transactionId): ?Payment;

    public function updateStatus(Payment $payment, PaymentStatus $status, ?string $transactionId = null, ?array $payload = null): Payment;
}
