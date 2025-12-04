<?php

namespace App\Filament\Widgets;

use App\Models\Load;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LoadsByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Loads by status';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $statusOrder = [
            'draft' => 'Draft',
            'posted' => 'Posted',
            'assigned' => 'Assigned',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $counts = Load::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        foreach ($statusOrder as $status => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$status] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Loads',
                    'data' => $data,
                    'backgroundColor' => [
                        '#94a3b8', // draft
                        '#f59e0b', // posted
                        '#0ea5e9', // assigned
                        '#6366f1', // in_transit
                        '#22c55e', // delivered
                        '#16a34a', // completed
                        '#ef4444', // cancelled
                    ],
                ],
            ],
        ];
    }
}
