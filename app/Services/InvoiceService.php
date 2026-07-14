<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate invoice PDF for the given order and store it in storage.
     * Returns the absolute path to the generated PDF.
     */
    public function generate(Order $order): string
    {
        $order->loadMissing('items', 'user');

        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        $relativePath = "invoices/invoice-{$order->invoice_number}.pdf";

        Storage::put($relativePath, $pdf->output());

        return Storage::path($relativePath);
    }
}
