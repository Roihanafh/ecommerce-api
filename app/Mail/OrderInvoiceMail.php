<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice #{$this->order->invoice_number} - Pembayaran Berhasil",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.invoice',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("invoice-{$this->order->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
