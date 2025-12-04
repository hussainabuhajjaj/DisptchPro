@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#111827',
        'font' => $brand['font'] ?? 'Inter, Arial, sans-serif',
        'company' => $brand['company'] ?? '',
        'address' => $brand['address'] ?? '',
    ];
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? ('Document - ' . ($load->load_number ?? '')) }}</title>
    <style>
        :root { --brand: {{ $brand['color'] }}; }
        body { font-family: {{ $brand['font'] }}; font-size: 12px; color: #0f172a; margin: 26px; }
        h1 { font-size: 22px; margin: 0; color: #0f172a; }
        h2 { font-size: 14px; margin: 12px 0 6px; color: #111; letter-spacing: 0.02em; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; }
        th { background: #f8fafc; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        .muted { color: #64748b; font-size: 11px; }
        .section { margin-bottom: 14px; }
        .flex { display: flex; justify-content: space-between; gap: 12px; }
        .box { border: 1px solid #e2e8f0; padding: 10px; border-radius: 10px; background: #fff; box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05); }
        .header { display:flex; justify-content:space-between; align-items:center; padding:12px; border-radius:12px; background: linear-gradient(135deg, var(--brand), #0f172a); color: #fff; }
        .header h1 { color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ $title ?? 'Document' }}</h1>
            <div class="muted" style="color:#e2e8f0;">Load {{ $load->load_number }} • {{ now()->toDayDateTimeString() }}</div>
            @if($brand['company'])
                <div style="color:#e2e8f0;">{{ $brand['company'] }} {{ $brand['address'] ? '• '.$brand['address'] : '' }}</div>
            @endif
        </div>
        @if(!empty($brand['logo']))
            <div><img src="{{ $brand['logo'] }}" alt="logo" style="max-height:60px; border-radius:8px; background:#fff; padding:4px;"></div>
        @endif
    </div>

    <div class="section flex" style="margin-top:12px;">
        <div class="box" style="flex:1">
            <h2>Parties</h2>
            <div><strong>Client:</strong> {{ $load->client?->name ?? 'N/A' }}</div>
            <div><strong>Carrier:</strong> {{ $load->carrier?->name ?? 'N/A' }}</div>
            <div><strong>Driver:</strong> {{ $load->driver?->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ ucfirst($load->status) }}</div>
        </div>
        <div class="box" style="flex:1">
            <h2>Financials</h2>
            <div><strong>Rate to client:</strong> ${{ number_format($load->rate_to_client ?? 0, 2) }}</div>
            <div><strong>Rate to carrier:</strong> ${{ number_format($load->rate_to_carrier ?? 0, 2) }}</div>
            <div><strong>Fuel surcharge:</strong> ${{ number_format($load->fuel_surcharge ?? 0, 2) }}</div>
            <div><strong>Margin:</strong> {{ number_format($load->margin ?? 0, 1) }}%</div>
        </div>
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
