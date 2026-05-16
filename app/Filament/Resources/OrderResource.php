<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_name'),
                        Forms\Components\TextInput::make('customer_email'),
                        Forms\Components\TextInput::make('customer_phone'),
                    ])->columns(3),
                Forms\Components\Section::make('Quote Details')
                    ->schema([
                        Forms\Components\TextInput::make('buy_buy_dept')
                            ->label('Buy Buy Department')
                            ->helperText('Department information for quote-based orders')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('quote_document_number')
                            ->label('Quote Document Number')
                            ->disabled(),
                        Forms\Components\TextInput::make('quote_expires_at')
                            ->label('Quote Expires')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Order::adminStatusLabels()[$state ?? ''] ?? $state)
                    ->color(fn (?string $state) => Order::adminStatusBadgeColor($state))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ])
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_vip_order')
                    ->label('VIP')
                    ->boolean(),
                Tables\Columns\TextColumn::make('buy_buy_dept')
                    ->label('Dept')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Order::adminStatusLabels()),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'In attesa',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                        'refunded' => 'Rimborsato',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markProcessing')
                        ->label('Segna come In Lavorazione')
                        ->action(fn($records) => $records->each->update(['status' => 'processing'])),
                    Tables\Actions\BulkAction::make('markShipped')
                        ->label('Segna come Spedito')
                        ->action(fn($records) => $records->each->update(['status' => 'shipped'])),
                    Tables\Actions\BulkAction::make('markCompleted')
                        ->label('Segna come Completato')
                        ->action(fn($records) => $records->each->update(['status' => 'completed'])),
                    Tables\Actions\BulkAction::make('markCancelled')
                        ->label('Segna come Annullato')
                        ->action(fn($records) => $records->each->update(['status' => 'cancelled'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
