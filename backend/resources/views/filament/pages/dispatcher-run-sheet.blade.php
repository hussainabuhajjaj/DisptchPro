@php
    $stops = $this->getStops();
    $dispatchers = $this->dispatcherOptions();
    $drivers = $this->driverOptions();
    $types = $this->typeOptions();
    $hasFilters = request()->hasAny(['dispatcher', 'driver', 'type', 'from', 'to', 'date']);
@endphp

<x-filament::page class="bg-gray-50 dark:bg-slate-900">
    <div class="space-y-4">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Dispatch</div>
                <div class="text-2xl font-semibold">Run sheet</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Stops due by date/time with dispatcher & driver context.</div>
            </div>
            <div class="flex gap-2">
                @if ($hasFilters)
                    <a href="{{ route('filament.admin.pages.dispatcher-run-sheet') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-xs font-semibold text-primary shadow hover:bg-primary/10">
                        Reset filters
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-6 items-end">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Dispatcher</label>
                    <select name="dispatcher" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                        <option value="">All</option>
                        @foreach ($dispatchers as $id => $name)
                            <option value="{{ $id }}" @selected(request('dispatcher') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Driver</label>
                    <select name="driver" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                        <option value="">All</option>
                        @foreach ($drivers as $id => $name)
                            <option value="{{ $id }}" @selected(request('driver') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Type</label>
                    <select name="type" class="w-full rounded-md border px-3 py-2 text-sm bg-white dark:bg-slate-800">
                        <option value="">All</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow hover:bg-primary-600">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border bg-white dark:bg-slate-900 shadow-sm">
            <div class="border-b px-4 py-3 flex items-center justify-between text-sm font-semibold">
                <div>Stops</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $stops->total() }} result(s)</div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-slate-800">
                        <tr class="border-b">
                            <th class="py-2 px-3 text-left">When</th>
                            <th class="py-2 px-3 text-left">Stop</th>
                            <th class="py-2 px-3 text-left">Load</th>
                            <th class="py-2 px-3 text-left">Dispatcher</th>
                            <th class="py-2 px-3 text-left">Driver</th>
                            <th class="py-2 px-3 text-left">Client / Carrier</th>
                            <th class="py-2 px-3 text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stops as $stop)
                            <tr class="border-b last:border-0">
                                <td class="py-2 px-3 text-sm">
                                    <div class="font-semibold">{{ optional($stop->date_from)->format('m/d H:i') ?? '—' }}</div>
                                    @if ($stop->appointment_time)
                                        <div class="text-xs text-gray-500">Appt {{ $stop->appointment_time }}</div>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-sm">
                                    <div class="font-semibold uppercase">{{ $stop->type ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $stop->city }}, {{ $stop->state }}</div>
                                    @if ($stop->facility_name)
                                        <div class="text-xs text-gray-500">{{ $stop->facility_name }}</div>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-sm">
                                    @if ($stop->loadRelation)
                                        <a href="{{ route('filament.admin.resources.loads.edit', $stop->loadRelation) }}" class="text-primary font-semibold hover:underline">
                                            {{ $stop->loadRelation->load_number }}
                                        </a>
                                        <div class="text-xs text-gray-500">Status: {{ ucfirst(str_replace('_', ' ', $stop->loadRelation->status)) }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-sm">{{ $stop->loadRelation?->dispatcher?->name ?? '—' }}</td>
                                <td class="py-2 px-3 text-sm">{{ $stop->loadRelation?->driver?->name ?? '—' }}</td>
                                <td class="py-2 px-3 text-sm">
                                    <div>{{ $stop->loadRelation?->client?->name ?? 'Client' }}</div>
                                    <div class="text-xs text-gray-500">{{ $stop->loadRelation?->carrier?->name ?? 'Carrier' }}</div>
                                </td>
                                <td class="py-2 px-3 text-xs text-gray-600 dark:text-gray-300">
                                    @if ($stop->instructions)
                                        {{ $stop->instructions }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-sm text-gray-500">No stops for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">
                {{ $stops->links() }}
            </div>
        </div>
    </div>
</x-filament::page>
