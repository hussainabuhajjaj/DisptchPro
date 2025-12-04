@php
    $hasExpiring = ($expiringCount ?? 0) > 0;
@endphp

<div
    class="rounded-xl border {{ $hasExpiring ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white' }} px-4 py-3 shadow-sm"
>
    <div class="flex items-start gap-3">
        <div
            class="mt-0.5 h-2 w-2 rounded-full {{ $hasExpiring ? 'bg-amber-500 animate-pulse' : 'bg-gray-300' }}"
            aria-hidden="true"
        ></div>
        <div class="flex-1 text-sm text-gray-700">
            @if ($hasExpiring)
                <div class="font-semibold text-amber-800">Credits expiring soon</div>
                <div class="text-amber-700">
                    {{ $expiringCount }} credit{{ $expiringCount === 1 ? '' : 's' }} worth
                    <span class="font-semibold">${{ number_format($expiringTotal, 2) }}</span>
                    expire within {{ $windowDays }} day{{ $windowDays === 1 ? '' : 's' }}.
                </div>
            @else
                <div class="font-semibold text-gray-800">No expiring credits</div>
                <div class="text-gray-600">All available credits are valid beyond the next period.</div>
            @endif
        </div>
    </div>
</div>
