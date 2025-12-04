<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class UmamiAnalyticsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.umami-analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static \UnitEnum|string|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 50;

    public function getHeading(): string
    {
        return 'Umami Analytics';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Open Umami')
                ->url(config('services.umami.dashboard_url') ?: '#')
                ->openUrlInNewTab()
                ->disabled(!config('services.umami.dashboard_url')),
        ];
    }
}
