<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;

class LiveMap extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Live Map';
    protected static UnitEnum|string|null $navigationGroup = 'Operations';
    protected static ?string $title = 'Live Map';

    protected string $view = 'filament.pages.live-map';
}
