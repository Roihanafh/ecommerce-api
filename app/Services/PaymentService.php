<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification as MidtransNotification;
use Midtrans\Snap;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected OrderRepositoryInterface $orderRepository,
    ) {
        MidtransConfig::$serverKey    = config('payment.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('payment.midtrans.production');
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    /**
     * Create a Midtrans Snap token for the given order.
     * Idempotent — returns existing payment if token was already generated.
     */
    public function createPayment(Order $order): Payment
    {
        $idempotencyKey = $this->buildIdempotencyKey($order);

        // Return existing payment if already created (idempotency)
        $existing = $this->paymentRepository->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        $snapData = $this->createSnapToken($order);

        return $this->paymentRepository->create(
            $order,
            $snapData['token'],
            $snapData['redirect_url'],
            $idempotencyKey,
        );
    }

    /**
     * Build and send transaction data to Midtrans, return snap token + redirect_url.
     */
    public function createSnapToken(Order $order): array
    {
        $order->loadMissing('items', 'user');

        $params = [
            'transaction_details' => [
                'order_id'     => $order->invoice_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email'      => $order->user->email,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id'       => (string) $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => $item->quantity,
                'name'     => $item->product_name,
            ])->toArray(),
        ];

        $snapToken   = Snap::getSnapToken($params);
        $redirectUrl = Snap::getSnapUrl($params);

        return [
            'token'        => $snapToken,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Verify and parse Midtrans webhook notification.
     * Returns structured notification data.
     */
    public function verifyNotification(): array
    {
        $notification = new MidtransNotification();

        return [
            'transaction_id'     => $notification->transaction_id,
            'order_id'           => $notification->order_id,       // this is invoice_number
            'transaction_status' => $notification->transaction_status,
            'fraud_status'       => $notification->fraud_status ?? null,
            'payment_type'       => $notification->payment_type,
            'gross_amount'       => $notification->gross_amount,
            'raw'                => (array) $notification,
        ];
    }

    /**
     * Mark payment and order as paid.
     */
    public function markPaid(Payment $payment, string $transactionId, array $payload): Payment
    {
        $payment = $this->paymentRepository->updateStatus(
            $payment,
            PaymentStatus::Paid,
            $transactionId,
            $payload,
        );

        $payment->order->update([
            'status'         => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
        ]);

        return $payment;
    }

    /**
     * Mark payment and order as failed.
     */
    public function markFailed(Payment $payment, string $transactionId, array $payload): Payment
    {
        $payment = $this->paymentRepository->updateStatus(
            $payment,
            PaymentStatus::Failed,
            $transactionId,
            $payload,
        );

        $payment->order->update([
            'payment_status' => PaymentStatus::Failed,
        ]);

        return $payment;
    }

    /**
     * Mark payment and order as expired.
     */
    public function markExpired(Payment $payment, string $transactionId, array $payload): Payment
    {
        $payment = $this->paymentRepository->updateStatus(
            $payment,
            PaymentStatus::Expired,
            $transactionId,
            $payload,
        );

        $payment->order->update([
            'payment_status' => PaymentStatus::Expired,
        ]);

        return $payment;
    }

    private function buildIdempotencyKey(Order $order): string
    {
        return (string) Str::uuid();
    }
}
