<x-filament::section>
    <div class="flex items-center justify-between gap-3 mb-3">
        <div>
            <div class="text-sm font-semibold text-slate-800">Active Loads</div>
            <div class="text-xs text-slate-500">Next stop, ETA, and last ping</div>
        </div>
    </div>

    @if(empty($loads))
        <x-filament::empty-state
            heading="No active loads"
            description="You have no assigned active loads."
            icon="heroicon-o-truck"
        />
    @else
        <div class="grid gap-3 md:grid-cols-2">
            @foreach($loads as $load)
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">
                                Load {{ $load['load_number'] ?? $load['id'] }}
                            </div>
                            <div class="text-[11px] inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-700">
                                {{ $load['status'] ?? '—' }}
                            </div>
                        </div>
                        @if($load['eta_minutes'])
                            <x-filament::badge color="info" size="sm">
                                ETA {{ $load['eta_minutes'] }} min
                            </x-filament::badge>
                        @endif
                    </div>

                    <div class="mt-2 text-xs text-slate-600 space-y-1">
                        @if($load['next_stop'])
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="font-semibold capitalize">{{ $load['next_stop']['type'] ?? 'Stop' }}</span>
                                <span>{{ $load['next_stop']['city'] ?? '' }} {{ $load['next_stop']['state'] ?? '' }}</span>
                                <span class="text-slate-500">{{ $load['next_stop']['date_from'] ?? '' }}</span>
                            </div>
                        @else
                            <div class="text-slate-500">No upcoming stop</div>
                        @endif

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1">
                                Last ping: {{ $load['last_ping_at'] ?? 'n/a' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
