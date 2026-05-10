<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Services\PaymentService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit_document_data')
                ->label('Dati documento / fiscali')
                ->icon('heroicon-o-document-text')
                ->form([
                    TextInput::make('customer_company_name')
                        ->label('Ragione sociale cliente'),
                    TextInput::make('customer_vat_number')
                        ->label('P.IVA cliente')
                        ->maxLength(32),
                    TextInput::make('customer_tax_code')
                        ->label('Codice fiscale cliente')
                        ->maxLength(32),
                    TextInput::make('quote_document_number')
                        ->label('N. preventivo (override)')
                        ->numeric(),
                    TextInput::make('quote_document_year')
                        ->label('Anno preventivo')
                        ->numeric(),
                    TextInput::make('invoice_document_number')
                        ->label('N. fattura (override)')
                        ->numeric(),
                    TextInput::make('invoice_document_year')
                        ->label('Anno fattura')
                        ->numeric(),
                ])
                ->fillForm(fn (Order $record): array => [
                    'customer_company_name' => $record->customer_company_name,
                    'customer_vat_number' => $record->customer_vat_number,
                    'customer_tax_code' => $record->customer_tax_code,
                    'quote_document_number' => $record->quote_document_number,
                    'quote_document_year' => $record->quote_document_year,
                    'invoice_document_number' => $record->invoice_document_number,
                    'invoice_document_year' => $record->invoice_document_year,
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update([
                        'customer_company_name' => $data['customer_company_name'] ?: null,
                        'customer_vat_number' => $data['customer_vat_number'] ?: null,
                        'customer_tax_code' => $data['customer_tax_code'] ?: null,
                        'quote_document_number' => isset($data['quote_document_number']) && $data['quote_document_number'] !== ''
                            ? (int) $data['quote_document_number']
                            : null,
                        'quote_document_year' => isset($data['quote_document_year']) && $data['quote_document_year'] !== ''
                            ? (int) $data['quote_document_year']
                            : null,
                        'invoice_document_number' => isset($data['invoice_document_number']) && $data['invoice_document_number'] !== ''
                            ? (int) $data['invoice_document_number']
                            : null,
                        'invoice_document_year' => isset($data['invoice_document_year']) && $data['invoice_document_year'] !== ''
                            ? (int) $data['invoice_document_year']
                            : null,
                    ]);
                    $this->notify('success', 'Dati documento aggiornati.');
                }),
            Actions\Action::make('download_order_pdf')
                ->label('Scarica modulo ordine')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (Order $record) => $record->order_confirmation_pdf_path
                    && Storage::disk('public')->exists($record->order_confirmation_pdf_path))
                ->action(function (Order $record) {
                    return Storage::disk('public')->download(
                        $record->order_confirmation_pdf_path,
                        'modulo_ordine_'.$record->order_number.'.pdf'
                    );
                }),
            Actions\Action::make('download_invoice_pdf')
                ->label('Scarica fattura')
                ->icon('heroicon-o-document-currency-euro')
                ->visible(fn (Order $record) => $record->invoice_pdf_path
                    && Storage::disk('public')->exists($record->invoice_pdf_path))
                ->action(function (Order $record) {
                    return Storage::disk('public')->download(
                        $record->invoice_pdf_path,
                        'fattura_'.$record->order_number.'.pdf'
                    );
                }),
            Actions\Action::make('update_status')
                ->label('Aggiorna stato')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
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
                ->action(function (Order $record, PaymentService $paymentService): void {
                    $paymentService->markOrderPaid($record);
                    $this->notify('success', 'Ordine segnato come pagato.');
                }),
            Actions\Action::make('sendStatusEmail')
                ->label('Invia email stato')
                ->icon('heroicon-o-envelope')
                ->action(function (Order $record) {
                    OrderStatusChanged::dispatch($record, $record->status, $record->status);
                    $this->notify('success', 'Email inviata');
                }),
        ];
    }

    public function getInfolistSchema(): array
    {
        return [
            Section::make('Dettagli Ordine')
                ->schema([
                    TextEntry::make('order_number'),
                    TextEntry::make('type')
                        ->label('Tipo')
                        ->formatStateUsing(fn (?string $state) => match ($state) {
                            'quote_request' => 'Richiesta preventivo',
                            'direct' => 'Ordine diretto',
                            default => $state ?? '—',
                        }),
                    TextEntry::make('customer_name'),
                    TextEntry::make('customer_email'),
                    TextEntry::make('customer_phone'),
                    TextEntry::make('status')
                        ->badge()
                        ->colors([
                            'warning' => 'pending',
                            'info' => 'processing',
                            'primary' => 'shipped',
                            'success' => 'completed',
                            'danger' => 'cancelled',
                        ]),
                    TextEntry::make('payment_status')
                        ->badge()
                        ->colors([
                            'warning' => 'pending',
                            'success' => 'paid',
                            'danger' => 'failed',
                            'info' => 'refunded',
                        ]),
                    TextEntry::make('payment_method'),
                ])->columns(3),

            Section::make('Cliente e fiscale')
                ->schema([
                    TextEntry::make('customer_company_name')
                        ->label('Ragione sociale'),
                    TextEntry::make('customer_vat_number')
                        ->label('P.IVA'),
                    TextEntry::make('customer_tax_code')
                        ->label('Codice fiscale'),
                ])->columns(3),

            Section::make('Numerazione documenti')
                ->schema([
                    TextEntry::make('quote_formatted')
                        ->label('Preventivo (n./anno)')
                        ->state(fn (Order $record): string => $record->formattedQuoteDocumentReference() ?? '—'),
                    TextEntry::make('quote_document_number')
                        ->label('N. preventivo'),
                    TextEntry::make('quote_document_year')
                        ->label('Anno preventivo'),
                    TextEntry::make('invoice_formatted')
                        ->label('Fattura (n./anno)')
                        ->state(fn (Order $record): string => $record->formattedInvoiceDocumentReference() ?? '—'),
                    TextEntry::make('invoice_document_number')
                        ->label('N. fattura'),
                    TextEntry::make('invoice_document_year')
                        ->label('Anno fattura'),
                ])->columns(3),

            Section::make('Riepilogo')
                ->schema([
                    TextEntry::make('subtotal')
                        ->money('EUR', locale: 'it_IT'),
                    TextEntry::make('shipping_cost')
                        ->money('EUR', locale: 'it_IT'),
                    TextEntry::make('tax')
                        ->money('EUR', locale: 'it_IT'),
                    TextEntry::make('total')
                        ->money('EUR', locale: 'it_IT'),
                ])->columns(4),

            Section::make('Indirizzi')
                ->schema([
                    TextEntry::make('shipping_address')
                        ->formatStateUsing(fn (?string $state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                        ->markdown(),
                    TextEntry::make('billing_address')
                        ->formatStateUsing(fn (?string $state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                        ->markdown(),
                ])->columns(2),

            Section::make('Articoli')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('line_description')
                                ->label('Descrizione riga'),
                            TextEntry::make('product_name')
                                ->label('Prodotto'),
                            TextEntry::make('product_sku')
                                ->label('SKU'),
                            TextEntry::make('vat_rate')
                                ->label('IVA %')
                                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '').'%' : '—'),
                            TextEntry::make('quantity'),
                            TextEntry::make('unit_price')
                                ->money('EUR', locale: 'it_IT'),
                            TextEntry::make('total')
                                ->money('EUR', locale: 'it_IT'),
                        ])->columns(7),
                ]),

            Section::make('Note')
                ->schema([
                    TextEntry::make('notes')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Order $record) => filled($record->notes)),
        ];
    }
}
