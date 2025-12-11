<x-filament-widgets::widget>
    <x-filament::section heading="ETA Countdown">
        @if($eta)
            <div class="flex flex-col gap-2">
                <div class="text-sm text-gray-500">Load {{ $eta['load_number'] }}</div>
                <div class="text-3xl font-bold text-primary-600">{{ $eta['eta_minutes'] }} min</div>
                <div class="text-xs text-gray-500">Last ping {{ $eta['last_ping'] ?? 'N/A' }}</div>
            </div>
        @else
            <div class="text-sm text-gray-500">No active load ETA available.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
