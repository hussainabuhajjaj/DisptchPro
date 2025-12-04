<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static UnitEnum|string|null $navigationGroup = 'Admin';
    protected static ?string $title = 'Activity Log';
    protected string $view = 'filament.pages.activity-log';

    public function getLogs()
    {
        return Activity::with('causer')->latest()->paginate(50);
    }
}
