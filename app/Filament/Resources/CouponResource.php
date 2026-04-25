<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                        ->action(fn (Forms\Set $set) => $set('code', 'SH-' . strtoupper(Str::random(8))))
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
                    ->state(fn (Coupon $record): string => $record->used_count . ' / ' . ($record->max_uses ?? '∞')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'inactive',
                        'warning' => 'scheduled',
                        'danger' => ['expired', 'exhausted'],
                    ]),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
                    ->label(fn (Coupon $record): string => $record->is_active ? 'Disattiva' : 'Attiva')
                    ->icon('heroicon-o-power')
                    ->action(fn (Coupon $record) => $record->update(['is_active' => ! $record->is_active])),
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
