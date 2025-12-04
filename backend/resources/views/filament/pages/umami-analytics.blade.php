@php
    $scriptUrl = config('services.umami.script_url');
    $siteId = config('services.umami.website_id');
    $dashboardUrl = config('services.umami.dashboard_url');
@endphp

<x-filament-panels::page>
    <div class="space-y-4">
        @if (!$scriptUrl || !$siteId)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900 dark:border-amber-800/60 dark:bg-amber-900/30 dark:text-amber-100">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm.375-12.118a.75.75 0 0 0-.75 0L3.41 7.269a.75.75 0 0 0-.353.64v8.182c0 .264.14.508.365.64l7.836 4.518c.232.134.518.134.75 0l7.836-4.518a.75.75 0 0 0 .365-.64V7.909a.75.75 0 0 0-.353-.64l-7.836-4.518Z" />
                    </svg>
                    <div class="space-y-1">
                        <p class="font-semibold">Umami is not configured.</p>
                        <p class="text-sm">Set <code>UMAMI_SCRIPT_URL</code> and <code>UMAMI_WEBSITE_ID</code> in your <code>.env</code>. Optionally set <code>UMAMI_DASHBOARD_URL</code> for the embedded dashboard.</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($dashboardUrl)
            <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm">
                <div class="border-b px-4 py-3 dark:border-gray-800">
                    <p class="text-sm font-semibold">Live dashboard</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Embedded from Umami</p>
                </div>
                <div class="aspect-video">
                    <iframe
                        src="{{ $dashboardUrl }}"
                        class="h-full w-full rounded-b-xl border-0"
                        allow="clipboard-write; fullscreen"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>
        @endif

        <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm">
            <div class="border-b px-4 py-3 dark:border-gray-800">
                <p class="text-sm font-semibold">Tracking snippet</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Rendered on all admin pages when configured.</p>
            </div>
            <div class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 space-y-2">
                @if ($scriptUrl && $siteId)
                    <p>Umami script is injected with site ID <code>{{ $siteId }}</code>.</p>
                    <p>Script URL: <code>{{ $scriptUrl }}</code></p>
                @else
                    <p class="text-amber-600">Pending configuration.</p>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
