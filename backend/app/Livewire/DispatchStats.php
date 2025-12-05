<?php

namespace App\Livewire;

use App\Models\Load;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

class DispatchStats extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public function statsInfolist(Schema $schema): Schema
    {
        $open = Load::whereNotIn('status', ['delivered', 'completed', 'cancelled'])->count();
        $unassigned = Load::where(function ($q) {
            $q->whereNull('carrier_id')->orWhereNull('driver_id');
        })->count();
        $avgMargin = Load::get()->average(fn (Load $load) => $load->margin);

        return $schema
            ->constantState([
                'open' => $open,
                'unassigned' => $unassigned,
                'avg_margin' => $avgMargin ? round($avgMargin, 1) . '%' : '—',
            ])
            ->columns(3)
            ->components([
                TextEntry::make('open')
                    ->label('Open loads')
                    ->badge()
                    ->color('primary'),
                TextEntry::make('unassigned')
                    ->label('Unassigned (carrier/driver)')
                    ->badge()
                    ->color('warning'),
                TextEntry::make('avg_margin')
                    ->label('Avg margin')
                    ->badge()
                    ->color('success'),
            ]);
    }

    public function render()
    {
        return view('livewire.dispatch-stats');
    }
}
