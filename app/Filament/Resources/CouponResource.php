<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\QRCodeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Loyalty';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('generate')
                        ->icon('heroicon-o-sparkles')
                        ->action(fn (Forms\Set $set) => $set('code', 'SH-'.strtoupper(Str::random(8))))
                ),
            Forms\Components\Select::make('type')
                ->options([
                    'fixed_points' => 'Punti fissi',
                    'fixed_money' => 'Importo fisso',
                    'percentage' => 'Percentuale',
                    'product_specific' => 'Prodotto specifico',
                ])
                ->live()
                ->required(),
            Forms\Components\TextInput::make('value')
                ->numeric()
                ->step(0.01)
                ->required(),
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'product_specific')
                ->required(fn (Forms\Get $get): bool => $get('type') === 'product_specific'),
            Forms\Components\TextInput::make('max_uses')->numeric()->minValue(1),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'fixed_points',
                        'success' => 'fixed_money',
                        'warning' => 'percentage',
                        'primary' => 'product_specific',
                    ]),
                Tables\Columns\TextColumn::make('value')->sortable(),
                Tables\Columns\TextColumn::make('usage_label')
                    ->label('Usi')
                    ->state(fn ($record): string => $record ? ($record->used_count.' / '.($record->max_uses ?? '∞')) : ''),
                Tables\Columns\TextColumn::make('qr_tokens_count')
                    ->label('QR Token')
                    ->state(fn ($record): int => $record ? $record->qrTokens()->valid()->count() : 0)
                    ->visible(fn ($record): bool => $record && $record->type === 'fixed_points'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'warning' => 'scheduled',
                        'danger' => ['expired', 'exhausted'],
                    ]),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\BadgeColumn::make('qr_available')
                    ->label('QR Code')
                    ->state(fn ($record): string => $record && $record->type === 'fixed_points' ? 'Disponibile' : 'N/A')
                    ->colors([
                        'success' => 'Disponibile',
                        'gray' => 'N/A',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'fixed_points' => 'Punti fissi',
                    'fixed_money' => 'Importo fisso',
                    'percentage' => 'Percentuale',
                    'product_specific' => 'Prodotto specifico',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle')
                    ->label(fn ($record): string => $record ? ($record->is_active ? 'Disattiva' : 'Attiva') : '')
                    ->icon('heroicon-o-power')
                    ->action(fn ($record) => $record?->update(['is_active' => ! $record->is_active])),
                Tables\Actions\Action::make('generate_qr')
                    ->label('Genera QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->visible(fn ($record): bool => $record && $record->type === 'fixed_points')
                    ->action(function ($record) {
                        if (! $record) {
                            return null;
                        }
                        $qrService = app(QRCodeService::class);

                        return $qrService->getQRCodeBase64($record);
                    })
                    ->modalHeading('QR Code per Coupon')
                    ->modalContent(fn ($record) => $record ? view('filament.components.qr-code-modal', [
                        'coupon' => $record,
                        'qrCode' => app(QRCodeService::class)->getQRCodeBase64($record),
                    ]) : null),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_generate')
                        ->label('Genera coupon')
                        ->form([
                            Forms\Components\TextInput::make('count')->numeric()->default(10)->minValue(1)->maxValue(500)->required(),
                            Forms\Components\TextInput::make('prefix')->default('SH')->required(),
                            Forms\Components\Select::make('type')->options([
                                'fixed_points' => 'Punti fissi',
                                'fixed_money' => 'Importo fisso',
                                'percentage' => 'Percentuale',
                                'product_specific' => 'Prodotto specifico',
                            ])->required()->live(),
                            Forms\Components\TextInput::make('value')->numeric()->step(0.01)->required(),
                            Forms\Components\Select::make('product_id')
                                ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable(),
                            Forms\Components\TextInput::make('max_uses')->numeric()->minValue(1),
                            Forms\Components\DateTimePicker::make('starts_at'),
                            Forms\Components\DateTimePicker::make('expires_at'),
                            Forms\Components\Toggle::make('is_active')->default(true),
                        ])
                        ->action(fn (array $data) => app(CouponService::class)->bulkGenerate($data)),
                ]),
                Tables\Actions\BulkAction::make('export_qr_codes')
                    ->label('Esporta QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Esporta QR Code')
                    ->modalDescription('Seleziona il formato di esportazione per i QR code dei coupon selezionati.')
                    ->form([
                        Forms\Components\Select::make('format')
                            ->label('Formato esportazione')
                            ->options([
                                'html' => 'HTML (consigliato - include le immagini QR)',
                                'text' => 'Testo (formato semplice)',
                            ])
                            ->default('html')
                            ->required()
                            ->helperText('HTML può essere stampato come PDF dal browser'),
                        Forms\Components\TextInput::make('filename')
                            ->label('Nome file')
                            ->default('qr-coupons-'.date('Y-m-d'))
                            ->required(),
                    ])
                    ->action(function (array $data, Collection $records) {
                        // Validate that at least one coupon is selected
                        if ($records->isEmpty()) {
                            throw ValidationException::withMessages([
                                'format' => 'Seleziona almeno un coupon per esportare i QR code.',
                            ]);
                        }

                        // Filter for fixed_points type
                        $fixedPointsCoupons = $records->filter(fn ($c) => $c->type === 'fixed_points');

                        if ($fixedPointsCoupons->isEmpty()) {
                            throw ValidationException::withMessages([
                                'format' => 'Nessun coupon di tipo "Punti fissi" selezionato. '.
                                           'Solo i coupon con punti fissi possono avere QR code.',
                            ]);
                        }

                        // Get the IDs of fixed_points coupons
                        $couponIds = $fixedPointsCoupons->pluck('id')->toArray();

                        // Redirect to download route
                        return redirect()->route('admin.coupons.export-qr', [
                            'coupon_ids' => $couponIds,
                            'format' => $data['format'],
                            'filename' => $data['filename'],
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
