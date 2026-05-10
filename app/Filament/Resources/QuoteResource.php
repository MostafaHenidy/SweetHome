<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Order;
use App\Services\QuoteService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class QuoteResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Shop Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Quote Information')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Quote Number')
                                    ->disabled(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending Review',
                                        'quote_sent' => 'Quote Sent',
                                        'quote_accepted' => 'Quote Accepted',
                                        'quote_rejected' => 'Quote Rejected',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required(),
                                Forms\Components\DateTimePicker::make('quote_expires_at')
                                    ->label('Quote Expires At')
                                    ->nullable(),
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->disabled()
                                            ->prefix('EUR')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('total')
                                            ->label('Total')
                                            ->disabled()
                                            ->prefix('EUR')
                                            ->numeric()
                                            ->step(0.01),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Section::make('Customer Information')
                            ->schema([
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Customer Name')
                                    ->disabled()
                                    ->required(),
                                Forms\Components\TextInput::make('customer_email')
                                    ->email()
                                    ->disabled()
                                    ->required(),
                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Phone')
                                    ->disabled(),
                                Forms\Components\KeyValue::make('shipping_address')
                                    ->label('Shipping Address')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Actions')
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generate_pdf')
                                        ->label('Generate PDF')
                                        ->icon('heroicon-o-document-arrow-down')
                                        ->action(function (Order $record, QuoteService $quoteService) {
                                            try {
                                                $record->load('items');
                                                $quoteService->generatePdf($record, advanceWorkflow: $record->status === 'pending');

                                                return true;
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Error generating PDF')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();

                                                return false;
                                            }
                                        }),
                                    Forms\Components\Actions\Action::make('send_quote')
                                        ->label('Send Quote')
                                        ->icon('heroicon-o-paper-airplane')
                                        ->requiresConfirmation()
                                        ->action(function (Order $record, QuoteService $quoteService) {
                                            try {
                                                $quoteService->sendQuote($record);
                                                Notification::make()
                                                    ->title('Quote Sent')
                                                    ->body('Quote has been sent to '.$record->customer_email)
                                                    ->success()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Error sending quote')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                    Forms\Components\Actions\Action::make('accept_quote')
                                        ->label('Accept Quote')
                                        ->icon('heroicon-o-check')
                                        ->color('success')
                                        ->visible(fn ($record) => $record->status === 'quote_sent')
                                        ->requiresConfirmation()
                                        ->action(function (Order $record, QuoteService $quoteService) {
                                            try {
                                                $quoteService->acceptQuote($record);
                                                Notification::make()
                                                    ->title('Quote Accepted')
                                                    ->body('Quote has been marked as accepted')
                                                    ->success()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Error accepting quote')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                    Forms\Components\Actions\Action::make('reject_quote')
                                        ->label('Reject Quote')
                                        ->icon('heroicon-o-x-mark')
                                        ->color('danger')
                                        ->visible(fn ($record) => $record->status === 'quote_sent')
                                        ->requiresConfirmation()
                                        ->form([
                                            Forms\Components\Textarea::make('reason')
                                                ->label('Rejection Reason')
                                                ->required(),
                                        ])
                                        ->action(function (Order $record, array $data, QuoteService $quoteService) {
                                            try {
                                                $quoteService->rejectQuote($record, $data['reason']);
                                                Notification::make()
                                                    ->title('Quote Rejected')
                                                    ->body('Quote has been marked as rejected')
                                                    ->danger()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Error rejecting quote')
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ]),
                            ]),
                        Forms\Components\Section::make('Quote Details')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->label('Quote Items')
                                    ->disabled()
                                    ->schema([
                                        Forms\Components\TextInput::make('product_name')
                                            ->label('Product'),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Quantity'),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->prefix('EUR')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('total_price')
                                            ->label('Total')
                                            ->prefix('EUR')
                                            ->numeric()
                                            ->step(0.01),
                                    ]),
                                Forms\Components\KeyValue::make('shipping_address')
                                    ->label('Shipping Address')
                                    ->disabled(),
                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('EUR', locale: 'it_IT'),
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => ['pending', 'quote_sent'],
                        'success' => 'quote_accepted',
                        'danger' => ['quote_rejected', 'cancelled'],
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('quote_expires_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Expires At')
                    ->sortable()
                    ->color(fn ($record) => $record->isQuoteExpired() ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Requested On')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'quote_sent' => 'Quote Sent',
                        'quote_accepted' => 'Quote Accepted',
                        'quote_rejected' => 'Quote Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query) => $query->whereNotNull('quote_expires_at')->where('quote_expires_at', '<', now())),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'quote_request'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_quote')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Download')
                    ->action(function (Order $record) {
                        // Check if PDF exists in public storage
                        if ($record->quote_pdf_path && Storage::disk('public')->exists($record->quote_pdf_path)) {
                            return Storage::disk('public')->download($record->quote_pdf_path, 'preventivo_'.$record->order_number.'.pdf');
                        }

                        Notification::make()
                            ->title('No PDF available')
                            ->body('Generate a PDF first')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_quotes')
                        ->label('Send Selected Quotes')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(function ($records, QuoteService $quoteService) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $quoteService->generatePdf($record);
                                    $quoteService->sendQuote($record);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Quotes Sent')
                                ->body("{$count} quotes have been sent")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListQuotes::route('/'),
            'view' => Pages\ViewQuote::route('/{record}'),
        ];
    }
}
