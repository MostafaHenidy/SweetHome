<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Prodotti', Product::count())
                ->description('Totale prodotti nel catalogo')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Ordini', Order::count())
                ->description('Totale ordini')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Recensioni in attesa', Review::where('status', 'pending')->count())
                ->description('Da approvare')
                ->icon('heroicon-o-star'),
            Stat::make('Entrate', '€' . number_format(Order::where('payment_status', 'paid')->sum('total'), 2, ',', '.'))
                ->description('Totale incassato')
                ->icon('heroicon-o-euro'),
        ];
    }
}
