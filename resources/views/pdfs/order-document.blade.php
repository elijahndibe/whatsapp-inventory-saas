<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { height: 56px; width: 56px; border-radius: 50%; object-fit: cover; }
        .business-name { font-size: 16px; font-weight: bold; }
        .muted { color: #6b7280; }
        .doc-title { font-size: 22px; font-weight: bold; text-transform: uppercase; text-align: right; }
        .doc-meta { text-align: right; color: #6b7280; }
        .section { margin-top: 24px; }
        .items-table th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .items-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .items-table .num { text-align: right; }
        .totals-table td { padding: 4px 8px; }
        .totals-table .label { color: #6b7280; text-align: right; }
        .totals-table .value { text-align: right; width: 100px; }
        .totals-table .grand td { border-top: 1px solid #1f2937; font-weight: bold; font-size: 14px; padding-top: 8px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #f3f4f6; color: #374151; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                @if ($business->logo)
                    <img class="logo" src="{{ storage_path('app/public/'.$business->logo) }}">
                @endif
                <div class="business-name" style="margin-top: 8px;">{{ $business->name }}</div>
                @if ($business->address)
                    <div class="muted">{{ collect([$business->address, $business->city, $business->state])->filter()->implode(', ') }}</div>
                @endif
                @if ($business->email)
                    <div class="muted">{{ $business->email }}</div>
                @endif
                @if ($business->phone)
                    <div class="muted">{{ $business->phone }}</div>
                @endif
            </td>
            <td style="width: 40%;">
                <div class="doc-title">{{ $documentType === 'receipt' ? 'Receipt' : 'Invoice' }}</div>
                <div class="doc-meta">#{{ $order->order_number }}</div>
                <div class="doc-meta">{{ $order->created_at->format('d M Y') }}</div>
                <div class="doc-meta" style="margin-top: 6px;">
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                        {{ $order->payment_status === 'paid' ? 'Paid' : ucfirst($order->payment_status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="section">
        <tr>
            <td style="width: 60%;">
                <div class="muted" style="font-size: 10px; text-transform: uppercase;">Billed To</div>
                <div style="font-weight: bold; margin-top: 2px;">{{ $order->customer->name }}</div>
                <div class="muted">{{ $order->customer->phone }}</div>
                @if ($order->customer->email)
                    <div class="muted">{{ $order->customer->email }}</div>
                @endif
                @if ($order->shipping_address)
                    <div class="muted">{{ $order->shipping_address }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table section">
        <thead>
            <tr>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Price</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ $order->currencySymbol() }}{{ number_format($item->price, 2) }}</td>
                    <td class="num">{{ $order->currencySymbol() }}{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table section" style="width: 260px; margin-left: auto;">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">{{ $order->currencySymbol() }}{{ number_format($order->subtotal, 2) }}</td>
        </tr>
        @if ($order->delivery_fee > 0)
            <tr>
                <td class="label">Delivery</td>
                <td class="value">{{ $order->currencySymbol() }}{{ number_format($order->delivery_fee, 2) }}</td>
            </tr>
        @endif
        @if ($order->discount > 0)
            <tr>
                <td class="label">Discount</td>
                <td class="value">-{{ $order->currencySymbol() }}{{ number_format($order->discount, 2) }}</td>
            </tr>
        @endif
        @if ($order->tax > 0)
            <tr>
                <td class="label">Tax</td>
                <td class="value">{{ $order->currencySymbol() }}{{ number_format($order->tax, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="label">Total</td>
            <td class="value">{{ $order->currencySymbol() }}{{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    @if ($order->customer_notes)
        <div class="section">
            <div class="muted" style="font-size: 10px; text-transform: uppercase;">Notes</div>
            <div>{{ $order->customer_notes }}</div>
        </div>
    @endif

    <div class="footer">
        {{ $documentType === 'receipt' ? 'Thank you for your payment.' : 'Thank you for your business.' }}
        — {{ $business->name }}
    </div>

</body>
</html>
