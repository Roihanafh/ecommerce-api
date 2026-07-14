<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #333; padding: 40px; font-size: 13px; }
        h1 { font-size: 22px; color: #1a1a2e; margin-bottom: 4px; }
        .subtitle { color: #888; margin-bottom: 24px; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .info-block p { margin-bottom: 4px; }
        .label { color: #888; font-size: 11px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f5f5f5; padding: 8px 10px; text-align: left; font-size: 12px; border-bottom: 2px solid #ddd; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 40px; font-size: 11px; color: #aaa; text-align: center; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px;
                        background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <h1>INVOICE</h1>
    <p class="subtitle">#{{ $order->invoice_number }}</p>

    <div class="info-grid">
        <div class="info-block">
            <p class="label">Kepada</p>
            <p><strong>{{ $order->user->name }}</strong></p>
            <p>{{ $order->user->email }}</p>
        </div>
        <div class="info-block" style="text-align: right">
            <p class="label">Tanggal</p>
            <p>{{ $order->created_at->format('d M Y') }}</p>
            <p style="margin-top: 8px"><span class="status-badge">PAID</span></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh sistem.
    </div>
</body>
</html>
