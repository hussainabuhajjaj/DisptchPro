@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#2563eb',
        'font' => $brand['font'] ?? 'Arial, sans-serif',
    ];
    $lineItems = $lineItems ?? [
        ['desc' => 'Line haul', 'amount' => $load->rate_to_client ?? 0],
        ['desc' => 'Fuel surcharge', 'amount' => $load->fuel_surcharge ?? 0],
    ];
    $total = collect($lineItems)->sum('amount');
    $invoiceNumber = $invoice_number ?? ('INV-' . ($load->load_number ?? ''));
    $dueDate = $due_date ?? null;
    $paymentTerms = $payment_terms ?? 'Net 30';
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? ('Invoice - ' . ($load->load_number ?? '')) }}</title>
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
            <div class="muted">Invoice #{{ $invoiceNumber }} • Load {{ $load->load_number }} • {{ now()->toDayDateTimeString() }}</div>
            @if($dueDate)
                <div class="muted">Due: {{ $dueDate }}</div>
            @endif
        </div>
        @if(!empty($brand['logo']))
            <div><img src="{{ $brand['logo'] }}" alt="logo" style="max-height:60px;"></div>
        @endif
    </div>

    <div class="section flex">
        <div class="box" style="flex:1">
            <h2>Bill To</h2>
            <div><strong>Client:</strong> {{ $load->client?->name ?? 'N/A' }}</div>
            <div class="muted">Terms: {{ $paymentTerms }}</div>
        </div>
        <div class="box" style="flex:1">
            <h2>Details</h2>
            <div><strong>Status:</strong> {{ ucfirst($load->status) }}</div>
            <div><strong>Distance:</strong> {{ $load->distance_miles ?? '—' }} mi</div>
            <div><strong>Margin:</strong> {{ number_format($load->margin ?? 0, 1) }}%</div>
        </div>
    </div>

    <div class="section">
        <h2>Line Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width:120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lineItems as $item)
                    <tr>
                        <td>{{ $item['desc'] }}</td>
                        <td>${{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr><td><strong>Total</strong></td><td>${{ number_format($total, 2) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Stops</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Facility</th>
                    <th>City/State</th>
                    <th>Window</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($load->stops as $stop)
                    <tr>
                        <td>{{ $stop->sequence }}</td>
                        <td>{{ ucfirst($stop->type) }}</td>
                        <td>{{ $stop->facility_name }}</td>
                        <td>{{ $stop->city }}, {{ $stop->state }}</td>
                        <td>
                            @if($stop->date_from)
                                {{ $stop->date_from->format('Y-m-d H:i') }}
                                @if($stop->date_to)
                                    – {{ $stop->date_to->format('Y-m-d H:i') }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No stops recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Notes</h2>
        <div class="box">
            <div><strong>Internal:</strong><br>{{ $load->internal_notes ?? '—' }}</div>
            <div style="margin-top:6px;"><strong>Driver:</strong><br>{{ $load->driver_notes ?? '—' }}</div>
        </div>
    </div>
</body>
</html>
