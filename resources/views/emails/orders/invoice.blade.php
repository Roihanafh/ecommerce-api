<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        h2 { color: #1a1a2e; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f0f0f0; padding: 8px; text-align: left; font-size: 13px; }
        td { padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
        .total { font-weight: bold; text-align: right; }
        .footer { margin-top: 24px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <h2>Pembayaran Berhasil</h2>
    <p>Halo {{ $order->user->name }},</p>
    <p>
        Terima kasih atas pembelian Anda. Berikut detail pesanan
        <strong>#{{ $order->invoice_number }}</strong>.
        Invoice PDF terlampir pada email ini.
    </p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total</td>
                <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Email ini dikirim otomatis. Jangan membalas email ini.</p>
    </div>
</div>
</body>
</html>
