@php
    $columns = $this->getLoadsByStatus();
    $labels = [
        'posted' => 'Posted',
        'assigned' => 'Assigned',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
    ];
    $statusColors = [
        'posted' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
        'assigned' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-200',
        'in_transit' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-200',
        'delivered' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200',
    ];
    $allLoads = collect($columns)->flatten(1);
    $unassignedCount = $allLoads->filter(fn ($load) => !$load->carrier_id || !$load->driver_id)->count();
    $avgMargin = $allLoads->count() ? round($allLoads->avg('margin'), 1) : null;
@endphp

<x-filament::page class="bg-gray-50 dark:bg-slate-900">
    <div class="space-y-4">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Quick board actions
            </div>
            <div class="flex gap-2">
                <a href="{{ route('filament.admin.resources.loads.create') }}" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow hover:bg-primary-600">
                    New Load
                </a>
                <a href="{{ route('filament.admin.resources.loads.index') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-xs font-semibold text-primary shadow hover:bg-primary/10">
                    Go to loads
                </a>
            </div>
        </div>

        <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <label for="dispatcher" class="text-sm text-gray-700 dark:text-gray-200">Dispatcher</label>
                    <select id="dispatcher" name="dispatcher" class="rounded-md border px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach ($dispatchers as $id => $name)
                            <option value="{{ $id }}" @selected(request('dispatcher') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="unassigned" value="1" class="rounded border-gray-300" @checked(request()->boolean('unassigned')) />
                    Unassigned only
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="late" value="1" class="rounded border-gray-300" @checked(request()->boolean('late')) />
                    Late only
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" name="at_risk" value="1" class="rounded border-gray-300" @checked(request()->boolean('at_risk')) />
                    At risk (next 6h)
                </label>
                <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow hover:bg-primary-600">
                    Apply
                </button>
                @if (request()->has('dispatcher') || request()->has('unassigned') || request()->has('late') || request()->has('at_risk'))
                    <a href="{{ route('filament.admin.pages.dispatch-board') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-xs font-semibold text-primary shadow hover:bg-primary/10">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm p-4">
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Open loads</div>
                <div class="mt-1 text-2xl font-semibold">{{ $allLoads->count() }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Visible across all statuses on this board</div>
            </div>
            <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm p-4">
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Unassigned</div>
                <div class="mt-1 text-2xl font-semibold text-amber-600 dark:text-amber-300">{{ $unassignedCount }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Missing carrier or driver</div>
            </div>
            <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm p-4">
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Avg margin</div>
                <div class="mt-1 text-2xl font-semibold">{{ $avgMargin !== null ? $avgMargin . '%' : '—' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Calculated from listed loads</div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-5">
            @foreach ($columns as $status => $loads)
                <div class="rounded-2xl border bg-white dark:bg-gray-900 shadow-sm">
                    <div class="border-b px-3 py-2 flex items-center justify-between text-sm font-semibold sticky top-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur z-10">
                        <span>{{ $labels[$status] ?? ucfirst($status) }}</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ is_countable($loads) ? count($loads) : 0 }}
                        </span>
                    </div>
                    <div class="divide-y max-h-[70vh] overflow-y-auto">
                        @forelse ($loads as $load)
                            <div class="p-3 space-y-2 hover:bg-primary/5 transition">
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('filament.admin.resources.loads.edit', $load) }}" class="text-sm font-semibold text-primary hover:underline">
                                        {{ $load->load_number }}
                                    </a>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                        {{ strtoupper($status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-300 flex flex-col gap-1">
                                    <span>{{ $load->client?->name ?? 'Client' }} · {{ $load->carrier?->name ?? 'Carrier' }}</span>
                                    <span>Driver: {{ $load->driver?->name ?? '—' }}</span>
                                    @php
                                        $lastCall = $load->checkCalls()->latest('reported_at')->first();
                                        $flags = app(\App\Filament\Pages\DispatchBoard::class)->slaFlags($load);
                                    @endphp
                                    @if($lastCall)
                                        <span class="text-[11px] text-gray-500">Last event: {{ $lastCall->status }} @ {{ $lastCall->reported_at?->format('m/d H:i') }}</span>
                                    @endif
                                    @if(!empty($flags))
                                        <span class="flex flex-wrap gap-1">
                                            @foreach($flags as $flag)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">{{ $flag }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11px] font-semibold flex-wrap">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200">
                                        Profit ${{ number_format($load->profit, 0) }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-200">
                                        Margin {{ $load->margin }}%
                                    </span>
                                    @php
                                        $slaColor = $load->route_status === 'late' ? 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200'
                                            : ($load->route_status === 'at_risk' ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200'
                                            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200');
                                        $slaLabel = $load->route_status === 'late' ? 'Late'
                                            : ($load->route_status === 'at_risk' ? 'At risk' : 'On time');
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 {{ $slaColor }}">
                                        {{ $slaLabel }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-xs text-gray-500">No loads</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament::page>
