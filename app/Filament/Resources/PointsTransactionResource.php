<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointsTransactionResource\Pages;
use App\Models\PointsTransaction;
use App\Services\PointsService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointsTransactionResource extends Resource
{
    protected static ?string $model = PointsTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Loyalty';
    protected static ?string $navigationLabel = 'Points Transactions';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Utente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('points')->sortable()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'earned',
                        'danger' => 'spent',
                        'warning' => 'admin_adjustment',
                    ]),
                Tables\Columns\TextColumn::make('order.order_number')->label('Ordine')->searchable(),
                Tables\Columns\TextColumn::make('description')->limit(60),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Utente')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'earned' => 'Guadagnati',
                        'spent' => 'Spesi',
                        'admin_adjustment' => 'Adeguamento admin',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Da'),
                        Forms\Components\DatePicker::make('until')->label('A'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointsTransactions::route('/'),
        ];
    }
}
