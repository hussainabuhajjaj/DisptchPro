<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use UnitEnum;

class ActivityLogPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static UnitEnum|string|null $navigationGroup = 'Admin';
    protected static ?string $title = 'Activity Log';
    protected string $view = 'filament.pages.activity-log';

    public function getLogs()
    {
        $query = Activity::with('causer')->latest();
        $perPage = (int) request()->input('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100]) ? $perPage : 50;

        if ($causer = request()->input('causer')) {
            $query->where('causer_id', $causer);
        }

        if ($action = request()->input('action')) {
            $query->where('description', 'like', '%' . $action . '%');
        }

        if ($subject = request()->input('subject')) {
            $query->where('subject_type', 'like', '%' . $subject . '%');
        }

        if ($from = request()->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = request()->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function causerOptions(): array
    {
        $ids = Activity::query()
            ->whereNotNull('causer_id')
            ->distinct()
            ->pluck('causer_id');

        return User::whereIn('id', $ids)->pluck('name', 'id')->toArray();
    }
}
