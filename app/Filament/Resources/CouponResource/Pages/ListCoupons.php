<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use App\Models\Product;
use App\Services\CouponService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulk_generate')
                ->label('Genera coupon')
                ->icon('heroicon-o-sparkles')
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
        ];
    }
}
