<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Carbon\Carbon;

class UsClockWidget extends Widget
{
    protected static ?string $heading = 'U.S. Time Zones';

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.us-clock-widget';

    public function getViewData(): array
    {
        $zones = [
            'New York (Eastern)' => 'America/New_York',
            'Chicago (Central)' => 'America/Chicago',
            'Denver (Mountain)' => 'America/Denver',
            'Los Angeles (Pacific)' => 'America/Los_Angeles',
        ];

        $now = Carbon::now();

        $times = [];
        foreach ($zones as $label => $tz) {
            $times[] = [
                'label' => $label,
                'time' => $now->copy()->setTimezone($tz)->format('h:i A'),
                'offset' => $now->copy()->setTimezone($tz)->format('T'),
                'city' => explode(' ', $label)[0],
                'tz' => $tz,
            ];
        }

        return ['zones' => $times];
    }
}
