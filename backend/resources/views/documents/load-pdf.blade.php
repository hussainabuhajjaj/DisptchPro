<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $load->load_number }} - {{ ucfirst($type) }} Doc</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        h2 { font-size: 14px; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .muted { color: #666; font-size: 11px; }
        .section { margin-bottom: 14px; }
        .flex { display: flex; justify-content: space-between; gap: 12px; }
        .box { border: 1px solid #ddd; padding: 8px; border-radius: 6px; }
    </style>
</head>
<body>
    <h1>Load {{ $load->load_number }} — {{ ucfirst($type) }} Document</h1>
    <div class="muted">Generated at {{ now()->toDayDateTimeString() }}</div>

    <div class="section flex">
        <div class="box" style="flex:1">
            <h2>Client / Carrier</h2>
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

    @php
        $accessorials = $load->accessorial_charges ?? [];
        $accTotal = collect($accessorials)->sum(fn($a) => $a['revenue'] ?? 0);
    @endphp
    @if (!empty($accessorials))
        <div class="section">
            <h2>Accessorials</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="width:130px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accessorials as $code => $acc)
                        <tr>
                            <td>{{ $acc['label'] ?? ucfirst(str_replace('_', ' ', $code)) }}</td>
                            <td>${{ number_format($acc['revenue'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight:700;">Accessorial total</td>
                        <td style="font-weight:700;">${{ number_format($accTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <h2>Notes</h2>
        <div class="box">
            <div><strong>Internal:</strong><br>{{ $load->internal_notes ?? '—' }}</div>
            <div style="margin-top:6px;"><strong>Driver:</strong><br>{{ $load->driver_notes ?? '—' }}</div>
        </div>
    </div>
</body>
</html>
