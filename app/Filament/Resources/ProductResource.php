<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components;
// Column removed - not available in Filament Forms
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informazioni Base')->schema([
                Grid::make(2)->schema([
                    Components\TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Components\Select::make('brand_id')
                        ->label('Brand')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload(),
                    Components\Select::make('gender')
                        ->label('Genere')
                        ->options([
                            'male' => 'Uomo',
                            'female' => 'Donna',
                            'unisex' => 'Unisex',
                            'kids' => 'Bambini',
                        ])
                        ->nullable(),
                    Components\Select::make('product_type')
                        ->label('Tipo Prodotto')
                        ->options([
                            'simple' => 'Semplice',
                            'variable' => 'Variabile',
                            'grouped' => 'Raggruppato',
                            'external' => 'Esterno/Affiliato',
                        ])
                        ->default('simple'),
                ]),
                Components\TextInput::make('name')
                    ->label('Nome Prodotto')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        return $set('slug', Str::slug($state));
                    })
                    ->maxLength(255),
                Components\Hidden::make('slug'),
                Components\Select::make('categories')
                    ->label('Categorie')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Components\Textarea::make('short_description')
                    ->label('Descrizione Breve')
                    ->rows(2)
                    ->maxLength(500),
                Components\Textarea::make('quote_line_text')
                    ->label('Testo riga preventivo / fattura')
                    ->helperText('Usato nelle righe PDF; se vuoto si usa la descrizione breve o il nome.')
                    ->rows(2)
                    ->columnSpanFull(),
                Grid::make(3)->schema([
                    Components\TextInput::make('vat_rate')
                        ->label('IVA predefinita (%)')
                        ->numeric()
                        ->step(0.01)
                        ->suffix('%'),
                    Components\TextInput::make('unit_of_measure')
                        ->label('Unità di misura')
                        ->maxLength(20)
                        ->placeholder('pz, m², …'),
                ]),
                Components\RichEditor::make('description')
                    ->label('Descrizione Completa'),
            ]),

            Section::make('Prezzo e Inventario')->schema([
                Grid::make(3)->schema([
                    Components\TextInput::make('price')
                        ->label('Prezzo (€)')
                        ->required()
                        ->numeric()
                        ->step(0.01)
                        ->prefix('€'),
                    Components\TextInput::make('compare_price')
                        ->label('Prezzo Scontato (€)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('€'),
                    Components\TextInput::make('cost_price')
                        ->label('Costo (€)')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('€'),
                    Components\TextInput::make('stock_quantity')
                        ->label('Giacenza')
                        ->numeric()
                        ->default(0),
                    Components\Toggle::make('track_stock')
                        ->label('Traccia Inventario')
                        ->default(true),
                ]),
            ]),

            Section::make('Varianti Prodotto')->schema([
                Components\Toggle::make('has_variants')
                    ->label('Questo prodotto ha varianti (taglia, colore, ecc.)')
                    ->default(false)
                    ->reactive(),

                Grid::make(2)->schema([
                    Section::make('Configura Opzioni')->schema([
                        Components\TextInput::make('variant_option_name')
                            ->label('Nome Opzione')
                            ->placeholder('Es. Taglia, Colore')
                            ->reactive(),
                        Components\Textarea::make('variant_option_values')
                            ->label('Valori (uno per riga)')
                            ->placeholder("S\nM\nL\nXL")
                            ->rows(4)
                            ->reactive(),
                        Components\Hidden::make('variant_options_json'),
                        ViewField::make('variant_options_display')
                            ->label('Opzioni Configurate')
                            ->view('filament.forms.components.variant-options-display'),
                    ])->columns(1),

                    Section::make('Anteprima Varianti')->schema([
                        ViewField::make('variants_preview')
                            ->view('filament.forms.components.variants-preview'),
                    ]),
                ])->visible(function (Get $get) {
                    return $get('has_variants');
                }),

                Components\Repeater::make('variants')
                    ->label('Varianti Manuali')
                    ->schema([
                        Components\TextInput::make('sku')->label('SKU Variante')->required(),
                        Components\TextInput::make('name')->label('Nome (es. Rosso - XL)')->required(),
                        Components\TextInput::make('price')->label('Prezzo')->numeric()->prefix('€'),
                        Components\TextInput::make('stock_quantity')->label('Giacenza')->numeric(),
                        Components\TextInput::make('weight')->label('Peso (g)')->numeric(),
                    ])->columns(3)
                    ->visible(fn (Get $get) => $get('has_variants')),
            ]),

            Section::make('SEO')->schema([
                Grid::make(2)->schema([
                    Components\TextInput::make('meta_title')
                        ->label('Meta Title')
                        ->maxLength(60)
                        ->live(),
                    Components\TextInput::make('focus_keyword')
                        ->label('Parola Chiave Focus')
                        ->maxLength(100)
                        ->live(),
                    Components\Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->rows(3)
                        ->maxLength(160)
                        ->live(),
                    Components\TextInput::make('slug')
                        ->label('URL Slug')
                        ->unique(ignoreRecord: true),
                ]),
            ]),

            Section::make('Spedizione')->schema([
                Grid::make(4)->schema([
                    Components\TextInput::make('weight')
                        ->label('Peso (kg)')
                        ->numeric()
                        ->step(0.001),
                    Components\TextInput::make('length')
                        ->label('Lunghezza (cm)')
                        ->numeric()
                        ->step(0.1),
                    Components\TextInput::make('width')
                        ->label('Larghezza (cm)')
                        ->numeric()
                        ->step(0.1),
                    Components\TextInput::make('height')
                        ->label('Altezza (cm)')
                        ->numeric()
                        ->step(0.1),
                    Components\Select::make('shipping_class')
                        ->label('Classe Spedizione')
                        ->options([
                            'standard' => 'Standard',
                            'express' => 'Express',
                            'oversized' => 'Over Size',
                            'fragile' => 'Fragile',
                        ])
                        ->nullable(),
                    Components\Toggle::make('free_shipping')
                        ->label('Spedizione Gratuita')
                        ->default(false),
                ]),
            ]),

            Section::make('Visibilita')->schema([
                Grid::make(2)->schema([
                    Components\Toggle::make('status')
                        ->label('Pubblicato')
                        ->default(false),
                    Components\DateTimePicker::make('published_at')
                        ->label('Pubblicazione Programmata')
                        ->nullable(),
                    Components\Toggle::make('is_featured')
                        ->label('In Evidenza')
                        ->default(false),
                    Components\Toggle::make('enable_reviews')
                        ->label('Consenti Recensioni')
                        ->default(true),
                ]),
            ]),

            Section::make('Immagini Prodotto')->schema([
                Components\FileUpload::make('images')
                    ->label('Galleria')
                    ->multiple()
                    ->image()
                    ->directory('products/images')
                    ->maxSize(5120)
                    ->panelLayout('grid')
                    ->reorderable()
                    ->appendFiles(),
            ]),

            Section::make('Etichette')->schema([
                Grid::make(3)->schema([
                    Components\Toggle::make('is_new_arrival')
                        ->label('Nuovo Arrivo')
                        ->default(false),
                    Components\Toggle::make('is_best_seller')
                        ->label('Best Seller')
                        ->default(false),
                    Components\Toggle::make('is_featured_product')
                        ->label('In Evidenza')
                        ->default(false),
                    Components\Toggle::make('is_on_sale')
                        ->label('In Sconto')
                        ->default(false),
                    Components\Toggle::make('is_limited')
                        ->label('Edizione Limitata')
                        ->default(false),
                    Components\Toggle::make('is_exclusive')
                        ->label('Esclusivo')
                        ->default(false),
                ]),
            ]),

            Section::make('Prodotti Correlati')->schema([
                Components\Select::make('related_products')
                    ->label('Seleziona Prodotti')
                    ->relationship('relatedProducts', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]),
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
