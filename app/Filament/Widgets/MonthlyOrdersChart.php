<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyOrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Ordini Mensili';

    protected static ?string $description = 'Andamento ordini e ricavi mensili';

    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $monthlyData = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as order_count, COALESCE(SUM(total), 0) as revenue")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        $months = [];
        $orderCounts = [];
        $revenues = [];

        foreach ($monthlyData as $data) {
            $months[] = $this->formatMonth($data->month);
            $orderCounts[] = $data->order_count;
            $revenues[] = (float) $data->revenue;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Ordini',
                    'data' => $orderCounts,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Ricavi (€)',
                    'data' => $revenues,
                    'type' => 'line',
                    'borderColor' => 'rgba(16, 185, 129, 0.8)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Numero Ordini',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Ricavi (€)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    private function formatMonth(string $monthString): string
    {
        $months = [
            '01' => 'Gen',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mag',
            '06' => 'Giu',
            '07' => 'Lug',
            '08' => 'Ago',
            '09' => 'Set',
            '10' => 'Ott',
            '11' => 'Nov',
            '12' => 'Dic',
        ];

        $parts = explode('-', $monthString);
        $monthNum = $parts[1] ?? '01';

        return $months[$monthNum] ?? 'N/A';
    }
}
