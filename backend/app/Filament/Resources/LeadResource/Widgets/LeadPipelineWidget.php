<?php

namespace App\Filament\Resources\LeadResource\Widgets;

use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class LeadPipelineWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $weekStart = Carbon::now()->startOfWeek();

        $total = Lead::count();
        $week = Lead::where('created_at', '>=', $weekStart)->count();
        $active = Lead::whereNotIn('status', ['lost'])->count();
        $lost = Lead::where('status', 'lost')->count();

        return [
            Card::make('Total leads', $total),
            Card::make('This week', $week),
            Card::make('Active', $active),
            Card::make('Lost', $lost),
        ];
    }
}
