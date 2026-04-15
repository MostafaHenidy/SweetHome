<?php

namespace App\Filament\Resources\ContactSubmissionResource\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContactSubmission extends ViewRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_read')
                ->label('Segna come letto')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn (ContactSubmission $record) => $record->status === 'new')
                ->action(function (ContactSubmission $record): void {
                    $record->update(['status' => 'read']);
                    $this->notify('success', 'Segnato come letto.');
                }),
            Actions\Action::make('mark_replied')
                ->label('Segna come risposto')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (ContactSubmission $record) => $record->status !== 'replied')
                ->action(function (ContactSubmission $record): void {
                    $record->update(['status' => 'replied']);
                    $this->notify('success', 'Segnato come risposto.');
                }),
        ];
    }

    public function getInfolistSchema(): array
    {
        return [
            \Filament\Infolists\Components\Section::make('Dettagli')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name'),
                    \Filament\Infolists\Components\TextEntry::make('email'),
                    \Filament\Infolists\Components\TextEntry::make('phone'),
                    \Filament\Infolists\Components\TextEntry::make('subject'),
                    \Filament\Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->colors([
                            'info' => 'new',
                            'warning' => 'read',
                            'success' => 'replied',
                        ]),
                    \Filament\Infolists\Components\TextEntry::make('created_at')
                        ->dateTime('d/m/Y H:i'),
                ])->columns(3),

            \Filament\Infolists\Components\Section::make('Messaggio')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('message')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
