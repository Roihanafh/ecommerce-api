<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderInvoiceMail;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        protected InvoiceService $invoiceService,
    ) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order->loadMissing('items', 'user');

        try {
            // Generate PDF invoice
            $pdfPath = $this->invoiceService->generate($order);

            // Send email with invoice attached
            Mail::to($order->user->email)
                ->send(new OrderInvoiceMail($order, $pdfPath));

        } catch (\Throwable $e) {
            Log::error('SendInvoiceListener failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
