<?php

namespace App\Filament\Resources\PointsTransactionResource\Pages;

use App\Filament\Resources\PointsTransactionResource;
use App\Models\User;
use App\Services\PointsService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;

class ListPointsTransactions extends ListRecords
{
    protected static string $resource = PointsTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('adjust_points')
                ->label('Adegua punti')
                ->icon('heroicon-o-plus-circle')
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('Utente')
                        ->options(User::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('points')
                        ->label('Punti (+/-)')
                        ->numeric()
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Descrizione')
                        ->default('Adeguamento manuale punti')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    app(PointsService::class)->adjustPoints(
                        User::findOrFail($data['user_id']),
                        (int) $data['points'],
                        $data['description'],
                    );
                }),
        ];
    }
}
