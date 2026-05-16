<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('index')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }
}
