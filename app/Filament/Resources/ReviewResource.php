<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('user_name')
                    ->required(),
                Forms\Components\TextInput::make('user_email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\Textarea::make('comment')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'approved' => 'Approvata',
                        'rejected' => 'Rifiutata',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Toggle::make('show_on_home')
                    ->label('Mostra in home')
                    ->helperText('Condivide questa recensione nel carosello della homepage.')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('rating')
                    ->color('warning')
                    ->icon('heroicon-s-star'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\IconColumn::make('show_on_home')
                    ->label('Home')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'approved' => 'Approvata',
                        'rejected' => 'Rifiutata',
                    ]),
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('show_on_home')
                    ->label('Mostra in home'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approva')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'approved']))
                    ->visible(fn (Review $record) => $record->status !== 'approved'),
                Tables\Actions\Action::make('reject')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'rejected']))
                    ->visible(fn (Review $record) => $record->status !== 'rejected'),
                Tables\Actions\Action::make('share_on_home')
                    ->label('Mostra in home')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->action(fn (Review $record) => $record->update([
                        'status' => 'approved',
                        'show_on_home' => true,
                    ]))
                    ->visible(fn (Review $record) => ! $record->show_on_home),
                Tables\Actions\Action::make('hide_from_home')
                    ->label('Nascondi dalla home')
                    ->icon('heroicon-o-eye-slash')
                    ->color('gray')
                    ->action(fn (Review $record) => $record->update(['show_on_home' => false]))
                    ->visible(fn (Review $record) => $record->show_on_home),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
