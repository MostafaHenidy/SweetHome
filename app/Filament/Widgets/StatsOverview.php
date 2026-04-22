<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Revenue', number_format(Order::where('payment_status', 'paid')->sum('total'), 2, ',', '.') . ' €')
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
    }
}