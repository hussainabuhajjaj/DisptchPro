<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UmamiEmbed extends Widget
{
    protected string $view = 'filament.widgets.umami-embed';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return filled(config('services.umami.script_url')) && filled(config('services.umami.website_id'));
    }
}
