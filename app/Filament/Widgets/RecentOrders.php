<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->with('user'))
            ->paginated(10)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label(__('widgets.ordine'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label(__('widgets.cliente'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('widgets.importo'))
                    ->money('EUR', locale: config('app.locale'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('widgets.stato'))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label(__('widgets.pagamento'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('widgets.data'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('widgets.in_attesa'),
                        'processing' => __('widgets.in_lavorazione'),
                        'shipped' => __('widgets.spedito'),
                        'completed' => __('widgets.completato'),
                        'cancelled' => __('widgets.annullato'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => __('widgets.in_attesa'),
                        'paid' => __('widgets.pagato'),
                        'failed' => __('widgets.fallito'),
                        'refunded' => __('widgets.rimborsato'),
                    ]),
            ]);
    }
}