<x-filament-widgets::widget>
    <x-filament::section class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 text-white">
        <style>
            .clock-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            .clock-card {
                position: relative;
                overflow: hidden;
                border-radius: 14px;
                padding: 14px;
                background: linear-gradient(145deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
                box-shadow: 0 14px 35px rgba(0,0,0,0.35);
                border: 1px solid rgba(255,255,255,0.08);
                backdrop-filter: blur(8px);
            }
            .clock-card::after {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08), transparent 45%);
                pointer-events: none;
            }
            .clock-label {
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: rgba(255,255,255,0.65);
                margin-bottom: 6px;
            }
            .clock-time {
                font-size: 28px;
                font-weight: 700;
                letter-spacing: 0.04em;
            }
            .clock-offset {
                font-size: 12px;
                color: rgba(255,255,255,0.7);
            }
        </style>
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-slate-300">U.S. Zones</div>
                <div class="text-lg font-semibold">Live Clocks</div>
            </div>
            <div class="text-xs text-slate-300">Auto-updates with server time</div>
        </div>
        <div class="clock-grid">
            @foreach($zones as $zone)
                <div class="clock-card">
                    <div class="clock-label">{{ $zone['label'] }}</div>
                    <div class="clock-time">{{ $zone['time'] }}</div>
                    <div class="clock-offset">{{ $zone['city'] }} • {{ $zone['offset'] }} • {{ $zone['tz'] }}</div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
