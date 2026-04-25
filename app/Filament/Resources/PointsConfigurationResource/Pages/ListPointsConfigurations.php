<?php

namespace App\Filament\Resources\PointsConfigurationResource\Pages;

use App\Filament\Resources\PointsConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPointsConfigurations extends ListRecords
{
    protected static string $resource = PointsConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
