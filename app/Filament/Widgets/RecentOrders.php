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
                    ->label('Ordine')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Importo')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'processing' => 'In lavorazione',
                        'shipped' => 'Spedito',
                        'completed' => 'Completato',
                        'cancelled' => 'Annullato',
                    ]),
            ]);
    }
}
