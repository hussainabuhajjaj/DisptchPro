<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;

class PipelineFlowBuilder extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Pipeline Builder';
    protected static ?string $title = 'Pipeline Builder';

    protected string $view = 'filament.pages.pipeline-flow-builder';
}
