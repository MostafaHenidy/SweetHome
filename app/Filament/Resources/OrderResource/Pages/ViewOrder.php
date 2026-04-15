<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('update_status')
                ->label('Aggiorna stato')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'In attesa',
                            'processing' => 'In lavorazione',
                            'shipped' => 'Spedito',
                            'completed' => 'Completato',
                            'cancelled' => 'Annullato',
                        ])
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update(['status' => $data['status']]);
                    $this->notify('success', 'Stato aggiornato.');
                }),
            Actions\Action::make('mark_paid')
                ->label('Segna come pagato')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Order $record) => $record->payment_status !== 'paid')
                ->action(function (Order $record): void {
                    $record->update(['payment_status' => 'paid']);
                    $this->notify('success', 'Ordine segnato come pagato.');
                }),
        ];
    }

    public function getInfolistSchema(): array
    {
        return [
            \Filament\Infolists\Components\Section::make('Dettagli Ordine')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('order_number'),
                    \Filament\Infolists\Components\TextEntry::make('customer_name'),
                    \Filament\Infolists\Components\TextEntry::make('customer_email'),
                    \Filament\Infolists\Components\TextEntry::make('customer_phone'),
                    \Filament\Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->colors([
                            'warning' => 'pending',
                            'info' => 'processing',
                            'primary' => 'shipped',
                            'success' => 'completed',
                            'danger' => 'cancelled',
                        ]),
                    \Filament\Infolists\Components\TextEntry::make('payment_status')
                        ->badge()
                        ->colors([
                            'warning' => 'pending',
                            'success' => 'paid',
                            'danger' => 'failed',
                            'info' => 'refunded',
                        ]),
                    \Filament\Infolists\Components\TextEntry::make('payment_method'),
                ])->columns(3),

            \Filament\Infolists\Components\Section::make('Riepilogo')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('subtotal')
                        ->money('EUR', locale: 'it_IT'),
                    \Filament\Infolists\Components\TextEntry::make('shipping_cost')
                        ->money('EUR', locale: 'it_IT'),
                    \Filament\Infolists\Components\TextEntry::make('tax')
                        ->money('EUR', locale: 'it_IT'),
                    \Filament\Infolists\Components\TextEntry::make('total')
                        ->money('EUR', locale: 'it_IT'),
                ])->columns(4),

            \Filament\Infolists\Components\Section::make('Indirizzi')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('shipping_address')
                        ->formatStateUsing(fn (?string $state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                        ->markdown(),
                    \Filament\Infolists\Components\TextEntry::make('billing_address')
                        ->formatStateUsing(fn (?string $state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                        ->markdown(),
                ])->columns(2),

            \Filament\Infolists\Components\Section::make('Articoli')
                ->schema([
                    \Filament\Infolists\Components\RepeatableEntry::make('items')
                        ->schema([
                            \Filament\Infolists\Components\TextEntry::make('product_name'),
                            \Filament\Infolists\Components\TextEntry::make('product_sku'),
                            \Filament\Infolists\Components\TextEntry::make('quantity'),
                            \Filament\Infolists\Components\TextEntry::make('unit_price')
                                ->money('EUR', locale: 'it_IT'),
                            \Filament\Infolists\Components\TextEntry::make('total')
                                ->money('EUR', locale: 'it_IT'),
                        ])->columns(5),
                ]),

            \Filament\Infolists\Components\Section::make('Note')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('notes')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Order $record) => filled($record->notes)),
        ];
    }
}
