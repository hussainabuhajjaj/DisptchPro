<x-filament-widgets::widget>
    <x-filament::section heading="Last Location">
        @if($location)
            <div class="text-sm text-gray-500">Load {{ $location['load_number'] }}</div>
            <div class="text-lg font-semibold">
                {{ number_format($location['lat'], 4) }}, {{ number_format($location['lng'], 4) }}
            </div>
            <div class="text-xs text-gray-500">Recorded at {{ $location['last_ping'] ?? 'N/A' }}</div>
        @else
            <div class="text-sm text-gray-500">No recent pings.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
