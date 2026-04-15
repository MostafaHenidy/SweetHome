<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Prodotto')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Generale')
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->slug('slug'),
                                Forms\Components\Hidden::make('slug'),
                                Forms\Components\Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Textarea::make('short_description')
                                    ->label('Descrizione breve'),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Descrizione')
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('status')
                                    ->label('Pubblicato')
                                    ->default(false),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Prezzo')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Prezzo (€)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01),
                                Forms\Components\TextInput::make('compare_price')
                                    ->label('Prezzo originale (€)')
                                    ->numeric()
                                    ->step(0.01),
                                Forms\Components\TextInput::make('cost_price')
                                    ->label('Costo (€)')
                                    ->numeric()
                                    ->step(0.01),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Inventario')
                            ->schema([
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Giacenza')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('track_stock')
                                    ->label('Traccia inventario')
                                    ->default(true),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Categorie')
                            ->schema([
                                Forms\Components\CheckboxList::make('categories')
                                    ->relationship('categories', 'name')
                                    ->columns(3)
                                    ->searchable(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Immagini')
                            ->schema([
                                Forms\Components\FileUpload::make('images')
                                    ->multiple()
                                    ->image()
                                    ->directory('products/images')
                                    ->maxSize(2048)
                                    ->panelLayout('grid')
                                    ->reorderable(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Attributi')
                            ->schema([
                                Forms\Components\Repeater::make('attributes')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome attributo')
                                            ->required(),
                                        Forms\Components\Textarea::make('values')
                                            ->label('Valori (separati da virgola)')
                                            ->required(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('track_stock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Pubblicato'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '0' => 'Bozza',
                        '1' => 'Pubblicato',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
