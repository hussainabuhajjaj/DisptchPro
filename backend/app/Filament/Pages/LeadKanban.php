<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;

class LeadKanban extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static UnitEnum|string|null $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Lead Kanban';
    protected static ?string $title = 'Lead Kanban';

    protected string $view = 'filament.pages.lead-kanban';
}
