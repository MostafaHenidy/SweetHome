<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Setting;
use App\Services\VipService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class VipManagement extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'VIP Management';
    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.vip-management';

    public ?array $qualificationData = [];

    public function mount(): void
    {
        $this->qualificationData = [
            'vip_minimum_order_threshold' => Setting::get('vip_minimum_order_threshold', 1000),
            'vip_time_period_days' => Setting::get('vip_time_period_days', 365),
            'vip_auto_qualify_enabled' => Setting::get('vip_auto_qualify_enabled', true),
            'points_per_euro_rate' => Setting::get('points_per_euro_rate', 1.00),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('VIP Qualification Settings')
                    ->schema([
                        TextInput::make('vip_minimum_order_threshold')
                            ->label('Minimum Order Threshold (€)')
                            ->numeric()
                            ->required()
                            ->default(1000)
                            ->helperText('Minimum order total within time period to qualify for VIP'),
                        TextInput::make('vip_time_period_days')
                            ->label('Time Period (days)')
                            ->numeric()
                            ->required()
                            ->default(365)
                            ->helperText('Look back period for order history'),
                        Toggle::make('vip_auto_qualify_enabled')
                            ->label('Enable Automatic VIP Qualification')
                            ->helperText('Automatically assign VIP status when customers reach threshold'),
                    ])->columns(2),
                Section::make('Points Configuration')
                    ->schema([
                        TextInput::make('points_per_euro_rate')
                            ->label('Points per Euro Rate')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(1.00)
                            ->helperText('Admin determines how many points equal 1 euro'),
                    ])->columns(2),
            ])
            ->statePath('qualificationData');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('is_vip', true))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->clickable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('vip_type')
                    ->label('VIP Type')
                    ->colors([
                        'info' => 'manual',
                        'success' => 'automatic',
                    ]),
                Tables\Columns\TextColumn::make('lifetime_spend')
                    ->label('Lifetime Spend')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable()
                    ->query(fn(Builder $query) => $query->withSum('orders', 'total')),
                Tables\Columns\TextColumn::make('points_balance')
                    ->label('Points Balance')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vip_assigned_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Assigned')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vipAssigner.name')
                    ->label('Assigned By')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vip_type')
                    ->label('VIP Type')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                    ]),
                Tables\Filters\Filter::make('vip_assigned_at')
                    ->label('Assignment Date')
                    ->form([
                        TextInput::make('from')
                            ->label('From')
                            ->date(),
                        TextInput::make('to')
                            ->label('To')
                            ->date(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['from']) {
                            $query->where('vip_assigned_at', '>=', $data['from']);
                        }
                        if ($data['to']) {
                            $query->where('vip_assigned_at', '<=', $data['to']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('remove_vip')
                    ->label('Remove VIP')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $vipService = app(VipService::class);
                        $vipService->removeVip($record, auth()->user(), 'Removed via admin panel');
                        $this->notify('success', "VIP status removed from {$record->name}");
                    }),
                Tables\Actions\Action::make('add_points')
                    ->label('Add Points')
                    ->form([
                        TextInput::make('points')
                            ->label('Points to Add')
                            ->numeric()
                            ->required()
                            ->min(0),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->default('Admin adjustment'),
                    ])
                    ->action(function (User $record, array $data) {
                        $vipService = app(VipService::class);
                        $vipService->addPoints($record, $data['points'], 'admin_adjustment', $data['description']);
                        $this->notify('success', "Added {$data['points']} points to {$record->name}");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_add_points')
                        ->label('Add Points to Selected')
                        ->form([
                            TextInput::make('points')
                                ->label('Points to Add')
                                ->numeric()
                                ->required()
                                ->min(0),
                            Textarea::make('description')
                                ->label('Description')
                                ->required()
                                ->default('Admin adjustment'),
                        ])
                        ->action(function ($records, array $data) {
                            $vipService = app(VipService::class);
                            $successCount = 0;

                            foreach ($records as $user) {
                                try {
                                    $vipService->addPoints($user, $data['points'], 'admin_adjustment', $data['description']);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    // Continue with other users
                                }
                            }

                            $this->notify('success', "Points added to {$successCount} VIP customers");
                        }),
                    Tables\Actions\BulkAction::make('bulk_remove_vip')
                        ->label('Remove VIP Status')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $vipService = app(VipService::class);
                            $successCount = 0;

                            foreach ($records as $user) {
                                try {
                                    $vipService->removeVip($user, auth()->user(), 'Bulk removal via admin panel');
                                    $successCount++;
                                } catch (\Exception $e) {
                                    // Continue with other users
                                }
                            }

                            $this->notify('success', "VIP status removed from {$successCount} customers");
                        }),
                ]),
            ]);
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        Setting::set('vip_minimum_order_threshold', $data['vip_minimum_order_threshold'], 'vip');
        Setting::set('vip_time_period_days', $data['vip_time_period_days'], 'vip');
        Setting::set('vip_auto_qualify_enabled', $data['vip_auto_qualify_enabled'], 'vip');
        Setting::set('points_per_euro_rate', $data['points_per_euro_rate'], 'vip');

        $this->notify('success', 'VIP settings saved successfully');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Forms\Components\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('saveSettings'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\VipStats::class,
        ];
    }
}