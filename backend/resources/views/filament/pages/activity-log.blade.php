<x-filament::page>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-600 dark:text-gray-300">
                <tr class="border-b">
                    <th class="py-2 pr-4 text-left">When</th>
                    <th class="py-2 pr-4 text-left">Causer</th>
                    <th class="py-2 pr-4 text-left">Action</th>
                    <th class="py-2 pr-4 text-left">Subject</th>
                    <th class="py-2 pr-4 text-left">Changes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getLogs() as $log)
                    <tr class="border-b last:border-0">
                        <td class="py-2 pr-4">{{ $log->created_at->toDayDateTimeString() }}</td>
                        <td class="py-2 pr-4">{{ $log->causer?->name ?? 'System' }}</td>
                        <td class="py-2 pr-4">{{ $log->description }}</td>
                        <td class="py-2 pr-4">
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        </td>
                        <td class="py-2 pr-4 text-xs text-gray-600 dark:text-gray-300">
                            @if($log->properties)
                                {{ json_encode($log->properties->toArray()) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $this->getLogs()->links() }}
    </div>
</x-filament::page>
