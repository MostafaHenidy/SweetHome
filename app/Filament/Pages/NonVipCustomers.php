<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\VipService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class NonVipCustomers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Non-VIP Customers';
    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?int $navigationSort = 25;

    protected static string $view = 'filament.pages.non-vip-customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('is_vip', false))
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withSum('orders', 'total')
                ->withMax('orders', 'created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label('Total Spend (All Time)')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_max_created_at')
                    ->label('Last Order')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('near_vip')
                    ->label('Near VIP Threshold (80%+)')
                    ->query(function (Builder $query) {
                        $threshold = setting('vip_minimum_order_threshold', 1000);
                        $minimumSpend = $threshold * 0.8;
                        $timePeriod = setting('vip_time_period_days', 365);

                        $query->whereHas('orders', function ($q) use ($minimumSpend, $timePeriod) {
                            $q->where('created_at', '>=', now()->subDays($timePeriod))
                              ->where('payment_status', 'paid')
                              ->groupBy('user_id')
                              ->havingRaw('SUM(total) >= ?', [$minimumSpend]);
                        });
                    }),
                Tables\Filters\SelectFilter::make('has_orders')
                    ->label('Has Orders')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'yes') {
                            $query->has('orders');
                        } elseif ($data['value'] === 'no') {
                            $query->doesntHave('orders');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('make_vip')
                    ->label('Make VIP')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Select::make('type')
                            ->label('VIP Type')
                            ->options([
                                'manual' => 'Manual (Assigned by Admin)',
                            ])
                            ->default('manual')
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->nullable(),
                    ])
                    ->action(function (User $record, array $data) {
                        $vipService = app(VipService::class);
                        $vipService->assignVip($record, $data['type'], auth()->user(), $data['reason']);
                        $this->notify('success', "{$record->name} is now a VIP customer");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('make_vip_bulk')
                        ->label('Make Selected VIP')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $vipService = app(VipService::class);
                            $successCount = 0;

                            foreach ($records as $user) {
                                try {
                                    $vipService->assignVip($user, 'manual', auth()->user(), 'Bulk assignment via admin panel');
                                    $successCount++;
                                } catch (\Exception $e) {
                                    // Continue with other users
                                }
                            }

                            $this->notify('success', "{$successCount} customers converted to VIP");
                        }),
                ]),
            ]);
    }
}