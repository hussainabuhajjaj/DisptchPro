<?php

namespace App\Filament\Driver\Pages;

use BackedEnum;
use Filament\Pages\Page;
use App\Filament\Driver\Widgets\ActiveLoadWidget;
use App\Filament\Driver\Widgets\TokenWidget;
use App\Filament\Driver\Widgets\EtaCountdownWidget;
use App\Filament\Driver\Widgets\LastLocationWidget;

class Dashboard extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $title = 'My Loads';
    protected static ?string $navigationLabel = 'Dashboard';

    protected string $view = 'filament.driver.pages.dashboard';

    protected static bool $shouldRegisterNavigation = true;

    protected function getHeaderWidgets(): array
    {
        return [
            ActiveLoadWidget::class,
            TokenWidget::class,
            EtaCountdownWidget::class,
            LastLocationWidget::class,
        ];
    }
}
