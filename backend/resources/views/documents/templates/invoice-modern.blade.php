@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#111827',
        'font' => $brand['font'] ?? 'Inter, system-ui, sans-serif',
        'company' => $brand['company'] ?? null,
        'address' => $brand['address'] ?? null,
    ];
    $items = $invoice->items ?? collect();
    $payments = $invoice->payments ?? collect();
    $total = $items->sum('amount');
    $paid = $payments->sum('amount');
    $balance = max($total - $paid, 0);
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: {{ $brand['font'] }}; font-size: 13px; color: #0f172a; margin: 32px; background: #f8fafc; }
        .card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 6px 30px rgba(15,23,42,0.06); }
        h1 { font-size: 22px; letter-spacing: 0.4px; margin: 0; color: {{ $brand['color'] }}; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #475569; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { text-align: left; font-size: 12px; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        td { padding: 10px 6px; border-bottom: 1px solid #e2e8f0; }
        .muted { color: #64748b; font-size: 12px; }
        .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 11px; }
        .totals td { border: none; padding: 4px 6px; }
        .totals { width: 50%; margin-left: auto; }
    </style>
</head>
<body>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <h1>Invoice</h1>
                <div class="muted">#{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</div>
                <div class="pill" style="margin-top:6px;">Due {{ optional($invoice->due_date)->format('Y-m-d') ?? '' }}</div>
            </div>
            <div style="text-align:right;">
                @if($brand['logo'])
                    <img src="{{ $brand['logo'] }}" alt="Logo" style="max-height:64px;">
                @endif
                @if($brand['company'])<div style="font-weight:700;margin-top:6px;">{{ $brand['company'] }}</div>@endif
                @if($brand['address'])<div class="muted" style="max-width:240px;">{!! nl2br(e($brand['address'])) !!}</div>@endif
            </div>
        </div>

        <div style="display:flex;gap:14px;margin-top:18px;">
            <div style="flex:1; background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <h2>Bill To</h2>
                <div style="font-weight:600;">{{ $invoice->client?->name }}</div>
            </div>
            <div style="flex:1; background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                <h2>Load</h2>
                <div style="font-weight:600;">{{ $invoice->loadRelation?->load_number }}</div>
            </div>
        </div>

        <h2>Line Items</h2>
        <table>
            <thead><tr><th>Description</th><th style="width:80px;">Qty</th><th style="width:110px;">Rate</th><th style="width:120px;text-align:right;">Amount</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->rate, 2) }}</td>
                    <td style="text-align:right;">${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table class="totals" style="margin-top:12px;">
            <tr><td>Subtotal</td><td style="text-align:right;">${{ number_format($total, 2) }}</td></tr>
            <tr><td>Paid</td><td style="text-align:right;">${{ number_format($paid, 2) }}</td></tr>
            <tr><td style="font-weight:700;">Balance</td><td style="text-align:right;font-weight:700;">${{ number_format($balance, 2) }}</td></tr>
        </table>

        <h2>Payments</h2>
        <table>
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th style="text-align:right;">Amount</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ optional($payment->paid_at)->format('Y-m-d') ?? '' }}</td>
                    <td>{{ $payment->method }}</td>
                    <td>{{ $payment->reference }}</td>
                    <td style="text-align:right;">${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
