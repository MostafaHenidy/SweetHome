<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    private ?array $stats = null;

    protected function getStats(): array
    {
        if ($this->stats !== null) {
            return $this->stats;
        }

        $revenue = DB::selectOne(
            'SELECT COALESCE(SUM(total), 0) as value FROM orders WHERE payment_status = ?',
            ['paid']
        );

        $this->stats = [
            Stat::make('Revenue', '€ ' . number_format($revenue->value, 2, ',', '.'))
                ->description('Totale incassato')
                ->icon('heroicon-o-currency-euro'),
            Stat::make('Ordini', Order::count())
                ->description('Totale ordini')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Clienti', User::count())
                ->description('Totale clienti')
                ->icon('heroicon-o-users'),
            Stat::make('Prodotti', Product::count())
                ->description('Totale prodotti')
                ->icon('heroicon-o-shopping-bag'),
        ];

        return $this->stats;
    }
}