<x-filament::section>
    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-sm font-semibold text-slate-800">API Token</div>
            <div class="text-xs text-slate-500">Used by the driver app for pings and jobs</div>
        </div>
    </div>
    <div class="mt-3 flex flex-col gap-2 text-sm text-slate-700">
        <div class="flex items-center gap-2">
            <span class="font-semibold">Token:</span>
            <span class="px-2 py-1 rounded bg-slate-100">{{ $this->maskedToken() }}</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="font-semibold">Expires:</span>
            <span>{{ $expiresAt ?? 'n/a' }}</span>
        </div>
    </div>
</x-filament::section>
