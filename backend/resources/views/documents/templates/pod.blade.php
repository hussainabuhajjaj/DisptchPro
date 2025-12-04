@php
    $brand = $brand ?? [
        'logo' => $brand['logo'] ?? null,
        'color' => $brand['color'] ?? '#2563eb',
        'font' => $brand['font'] ?? 'Arial, sans-serif',
    ];
    $showSignatures = $show_signatures ?? true;
    $deliveryDate = $delivery_date ?? null;
    $recipientName = $recipient_name ?? null;
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? ('Delivery Receipt - ' . ($load->load_number ?? '')) }}</title>
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
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
            <h1>Delivery Receipt</h1>
            <div class="muted">Load {{ $load->load_number }} • {{ now()->toDayDateTimeString() }}</div>
        </div>
        @if(!empty($brand['logo']))
            <div><img src="{{ $brand['logo'] }}" alt="logo" style="max-height:60px;"></div>
        @endif
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
            @if($deliveryDate)
                <div style="margin-top:6px;"><strong>Delivered at:</strong> {{ $deliveryDate }}</div>
            @endif
            @if($recipientName)
                <div style="margin-top:6px;"><strong>Received by:</strong> {{ $recipientName }}</div>
            @endif
        </div>
    </div>

    @if($showSignatures)
        <div class="section flex">
            <div class="box" style="flex:1;height:90px;">
                <strong>Receiver Signature</strong><br><br>
                ____________________________
            </div>
            <div class="box" style="flex:1;height:90px;">
                <strong>Driver Signature</strong><br><br>
                ____________________________
            </div>
        </div>
    @endif
</body>
</html>
