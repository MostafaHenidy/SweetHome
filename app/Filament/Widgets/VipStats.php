<?php

namespace App\Filament\Widgets;

use App\Services\VipService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VipStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $vipService = app(VipService::class);
        $stats = $vipService->getVipStatistics();

        return [
            Stat::make('VIP Customers', $stats['vip_users'])
                ->description("{$stats['vip_percentage']}% of total customers")
                ->color('success'),
            Stat::make('Manual VIP', $stats['vip_manual'])
                ->description('Assigned by admin'),
            Stat::make('Auto VIP', $stats['vip_automatic'])
                ->description('Qualified automatically'),
            Stat::make('Total Customers', $stats['total_users'])
                ->color('gray'),
        ];
    }
}