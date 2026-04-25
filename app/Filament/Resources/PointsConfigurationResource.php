<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointsConfigurationResource\Pages;
use App\Models\PointsConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PointsConfigurationResource extends Resource
{
    protected static ?string $model = PointsConfiguration::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Loyalty';
    protected static ?string $navigationLabel = 'Points Configuration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('points_per_euro')
                ->label('Punti per euro')
                ->numeric()
                ->step(0.01)
                ->required(),
            Forms\Components\TextInput::make('points_per_order_percentage')
                ->label('% ordine convertita in punti')
                ->numeric()
                ->step(0.01)
                ->suffix('%')
                ->required(),
            Forms\Components\Toggle::make('is_active')
                ->label('Attiva')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('points_per_euro')->label('Punti / Euro'),
                Tables\Columns\TextColumn::make('points_per_order_percentage')->label('% Ordine')->suffix('%'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Attiva'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointsConfigurations::route('/'),
            'create' => Pages\CreatePointsConfiguration::route('/create'),
            'edit' => Pages\EditPointsConfiguration::route('/{record}/edit'),
        ];
    }
}
