<x-filament::page>
    <x-filament::section heading="Dispatch Snapshot" description="Quick health of the board.">
        @livewire('dispatch-stats')
    </x-filament::section>

    <x-filament::section heading="Dispatch Board" description="Interactive grid of loads with filters and quick links.">
        @livewire('dispatch-board-table')
    </x-filament::section>
</x-filament::page>
