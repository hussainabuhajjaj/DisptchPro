@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#2563eb',
        'font' => $brand['font'] ?? 'Arial, sans-serif',
    ];
    $brokerRef = $broker_ref ?? '';
    $equipment = $equipment ?? '';
    $contactName = $contact_name ?? '';
    $contactPhone = $contact_phone ?? '';
    $showSignatures = $show_signatures ?? false;
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? ('Rate Confirmation - ' . ($load->load_number ?? '')) }}</title>
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
        .totals { float: right; width: 50%; margin-top: 10px; }
        .totals td { border: none; }
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
            <h1>Rate Confirmation</h1>
            <div class="muted">Load {{ $load->load_number }} • {{ now()->toDayDateTimeString() }}</div>
        </div>
        @if(!empty($brand['logo']))
            <div><img src="{{ $brand['logo'] }}" alt="logo" style="max-height:60px;"></div>
        @endif
    </div>

    <div class="section flex">
        <div class="box" style="flex:1">
            <h2>Client</h2>
            <div><strong>Name:</strong> {{ $load->client?->name ?? 'N/A' }}</div>
            <div><strong>Contact:</strong> {{ $load->client?->contact_name ?? '' }}</div>
        </div>
        <div class="box" style="flex:1">
            <h2>Carrier</h2>
            <div><strong>Name:</strong> {{ $load->carrier?->name ?? 'N/A' }}</div>
            <div><strong>Driver:</strong> {{ $load->driver?->name ?? 'N/A' }}</div>
            @if($brokerRef)
                <div><strong>Broker Ref:</strong> {{ $brokerRef }}</div>
            @endif
            @if($equipment)
                <div><strong>Equipment:</strong> {{ $equipment }}</div>
            @endif
            @if($contactName || $contactPhone)
                <div><strong>Contact:</strong> {{ $contactName }} {{ $contactPhone ? "({$contactPhone})" : '' }}</div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Stops & Instructions</h2>
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
        <div class="box" style="margin-top:10px;">
            <strong>Driver Instructions:</strong><br>
            {{ $load->driver_notes ?? '—' }}
        </div>
    </div>

    <div class="section">
        <h2>Pay Terms</h2>
        <table class="totals">
            <tr><td><strong>Line haul</strong></td><td>${{ number_format($load->rate_to_client ?? 0, 2) }}</td></tr>
            <tr><td><strong>Fuel</strong></td><td>${{ number_format($load->fuel_surcharge ?? 0, 2) }}</td></tr>
            <tr><td><strong>Total</strong></td><td>${{ number_format(($load->rate_to_client ?? 0) + ($load->fuel_surcharge ?? 0), 2) }}</td></tr>
        </table>
    </div>

    @if($showSignatures)
        <div class="section flex">
            <div class="box" style="flex:1;height:90px;">
                <strong>Dispatcher Signature</strong><br><br>
                ____________________________
            </div>
            <div class="box" style="flex:1;height:90px;">
                <strong>Carrier Signature</strong><br><br>
                ____________________________
            </div>
        </div>
    @endif
</body>
</html>
