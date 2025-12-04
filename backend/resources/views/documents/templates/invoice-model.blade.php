@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#2563eb',
        'font' => $brand['font'] ?? 'Arial, sans-serif',
        'company' => $brand['company'] ?? null,
        'address' => $brand['address'] ?? null,
    ];
    $items = $invoice->items ?? collect();
    $payments = $invoice->payments ?? collect();
    $total = $items->sum('amount');
    $paid = $payments->sum('amount');
    $balance = max($total - $paid, 0);
    $creditsAvailable = \App\Models\CreditBalance::where('entity_type','client')->where('entity_id',$invoice->client_id)->sum('remaining');
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        :root { --brand: {{ $brand['color'] }}; }
        body { font-family: {{ $brand['font'] }}; font-size: 12px; color: #111; margin: 26px; }
        h1 { font-size: 20px; margin-bottom: 4px; color: var(--brand); }
        h2 { font-size: 14px; margin: 12px 0 6px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #f9fafb; }
        .muted { color: #666; font-size: 11px; }
        .section { margin-bottom: 14px; }
        .flex { display: flex; justify-content: space-between; gap: 12px; }
        .box { border: 1px solid #e5e7eb; padding: 10px; border-radius: 6px; background: #fff; }
        .totals { float: right; width: 45%; margin-top: 10px; }
        .totals td { border: none; }
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
            <h1>Invoice</h1>
            <div class="muted">#{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</div>
            <div class="muted">Issue: {{ optional($invoice->issue_date ?? $invoice->invoice_date)->format('Y-m-d') ?? '' }}</div>
            <div class="muted">Due: {{ optional($invoice->due_date)->format('Y-m-d') ?? '' }}</div>
            <div class="muted">Credits available: ${{ number_format($creditsAvailable, 2) }}</div>
        </div>
        <div style="text-align:right;">
            @if($brand['logo'])
                <img src="{{ $brand['logo'] }}" alt="Logo" style="max-height:60px;">
            @endif
            @if($brand['company'])<div style="font-weight:700;">{{ $brand['company'] }}</div>@endif
            @if($brand['address'])<div class="muted" style="max-width:220px;">{!! nl2br(e($brand['address'])) !!}</div>@endif
        </div>
    </div>

    <div class="flex section">
        <div class="box" style="width:48%;">
            <h2>Bill To</h2>
            <div>{{ $invoice->client?->name }}</div>
        </div>
        <div class="box" style="width:48%;">
            <h2>Load</h2>
            <div>{{ $invoice->loadRelation?->load_number }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Line Items</h2>
        <table>
            <thead><tr><th>Description</th><th style="width:70px;">Qty</th><th style="width:100px;">Rate</th><th style="width:100px;">Amount</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->rate, 2) }}</td>
                    <td>${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section" style="text-align:right;">
        <table class="totals">
            <tr><td>Subtotal</td><td style="text-align:right;">${{ number_format($total, 2) }}</td></tr>
            <tr><td>Paid</td><td style="text-align:right;">${{ number_format($paid, 2) }}</td></tr>
            <tr><td style="font-weight:700;">Balance</td><td style="text-align:right;font-weight:700;">${{ number_format($balance, 2) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Payments</h2>
        <table>
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ optional($payment->paid_at)->format('Y-m-d') ?? '' }}</td>
                    <td>{{ $payment->method }}</td>
                    <td>{{ $payment->reference }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
