<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Order;
use App\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(Order $order, string $snapToken, string $redirectUrl, string $idempotencyKey): Payment
    {
        return Payment::create([
            'order_id'         => $order->id,
            'provider'         => 'midtrans',
            'snap_token'       => $snapToken,
            'redirect_url'     => $redirectUrl,
            'amount'           => $order->total_amount,
            'status'           => PaymentStatus::Pending,
            'idempotency_key'  => $idempotencyKey,
        ]);
    }

    public function findByIdempotencyKey(string $key): ?Payment
    {
        return Payment::where('idempotency_key', $key)->first();
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->firstOrFail();
    }

    public function updateStatus(Payment $payment, PaymentStatus $status, ?string $transactionId = null, ?array $payload = null): Payment
    {
        $payment->update(array_filter([
            'status'         => $status,
            'transaction_id' => $transactionId,
            'payload'        => $payload,
        ], fn ($v) => $v !== null));

        return $payment;
    }
}
