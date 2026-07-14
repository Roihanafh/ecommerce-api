<?php

namespace App\Jobs;

use App\Events\OrderPaid;
use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessMidtransWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        protected array $payload
    ) {}

    public function handle(
        PaymentService $paymentService,
        PaymentRepositoryInterface $paymentRepository,
        ProductRepositoryInterface $productRepository,
    ): void {
        try {
            // ────────────────────────────────────────────────
            // 1. Verify Signature (sudah dilakukan di controller,
            //    job hanya memproses payload yang sudah terverifikasi)
            // ────────────────────────────────────────────────
            $notification = $paymentService->verifyNotification();

            $transactionId = $notification['transaction_id'];
            $status        = $notification['transaction_status'];
            $fraud         = $notification['fraud_status'];
            $raw           = $notification['raw'];

            // ────────────────────────────────────────────────
            // 2. Update Payment
            // ────────────────────────────────────────────────
            $payment = $paymentRepository->findByTransactionId($transactionId);

            if (! $payment) {
                Log::warning('ProcessMidtransWebhook: payment not found', [
                    'transaction_id' => $transactionId,
                ]);
                return;
            }

            // ────────────────────────────────────────────────
            // Idempotency check — skip jika sudah paid
            // ────────────────────────────────────────────────
            if ($payment->status->value === 'paid') {
                Log::info('ProcessMidtransWebhook: already paid, skipping', [
                    'transaction_id' => $transactionId,
                ]);
                return;
            }

            $isPaid = ($status === 'settlement')
                || ($status === 'capture' && $fraud === 'accept');

            if ($isPaid) {
                // ────────────────────────────────────────────────
                // 3. Update Order (markPaid handles order update)
                // ────────────────────────────────────────────────
                $payment = $paymentService->markPaid($payment, $transactionId, $raw);
                $order   = $payment->order->load('items.product', 'user');

                // ────────────────────────────────────────────────
                // 4. Update Stock
                // ────────────────────────────────────────────────
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $productRepository->decreaseStock($item->product, $item->quantity);
                    }
                }

                // ────────────────────────────────────────────────
                // 5. Fire event — listener handle PDF + email
                //    Job tidak perlu tahu cara kirim email
                // ────────────────────────────────────────────────
                OrderPaid::dispatch($order);

            } elseif (\in_array($status, ['deny', 'cancel', 'failure'], true)) {
                $paymentService->markFailed($payment, $transactionId, $raw);

            } elseif ($status === 'expire') {
                $paymentService->markExpired($payment, $transactionId, $raw);
            }

        } catch (\Throwable $e) {
            Log::error('ProcessMidtransWebhook failed', [
                'error'   => $e->getMessage(),
                'payload' => $this->payload,
            ]);

            throw $e;
        }
    }
}
