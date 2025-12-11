<?php
 
namespace App\Filament\Pages;

/**
 * Pulse dashboard disabled per user request to avoid route errors.
 * Kept as a placeholder but not registered in navigation or discovery.
 */
class PulseDashboard extends \Filament\Pages\Dashboard
{
    protected static bool $shouldRegisterNavigation = false;
    protected static bool $isDiscovered = false;
}
