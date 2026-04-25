# Product Create/Edit Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a comprehensive two-column product create/edit page for the Laravel/Filament admin panel

**Architecture:** Two-column layout (main content 2/3 + sidebar 1/3) with dynamic sections. Main content has Basic Info, Pricing & Inventory, Variants, SEO, Shipping. Sidebar has Visibility, Parent Category, Images, Tags/Labels, Related Products.

**Tech Stack:** Laravel 12, Filament 3.2, Spatie Media Library

---

## Context: Current State

### Products Table Columns (already exist)
`id`, `sku`, `name`, `slug`, `short_description`, `description`, `price`, `compare_price`, `cost_price`, `brand_id`, `stock_quantity`, `track_stock`, `status`, `sort_order`, `created_at`, `updated_at`

### Products Table Columns (added by migration 000001)
`gender`, `product_type`, `meta_title`, `meta_description`, `focus_keyword`, `weight`, `length`, `width`, `height`, `shipping_class`, `free_shipping`, `enable_reviews`, `is_taxable`, `is_featured`, `published_at`

### Existing Related Tables (migrations 000002-000007)
- `product_variants` - variants with sku, price, stock, etc.
- `product_variant_options` - option values for variants (e.g., "Red", "XL")
- `product_filters` - filterable attributes
- `product_labels` - labels (New Arrival, Best Seller, etc.)
- `related_products` - cross-sell relationships
- `product_images` - gallery images with sort order

### Product Model (current state)
```php
// Has Media Library traits, brand/category relations
// Existing: sku, name, slug, short_description, description, price, compare_price, cost_price,
//          brand_id, stock_quantity, track_stock, status, sort_order
// Has: brand(), categories(), attributes(), reviews(), orderItems()
// Media: 'images' collection with thumb/medium conversions
```

### ProductResource (current state)
Uses Tabs layout: Generale, Prezzo, Inventario, Categorie, Immagini, Attributi

---

## Task 1: Product Model - Add Relationships

**Files:**
- Modify: `app/Models/Product.php`

Add the following relationships to the `Product` model:

```php
// Variants
public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class);
}

// Variant options (all options for all variants)
public function variantOptions(): HasMany
{
    return $this->hasMany(ProductVariantOption::class);
}

// Filters
public function filters(): HasMany
{
    return $this->hasMany(ProductFilter::class);
}

// Labels
public function labels(): HasMany
{
    return $this->hasMany(ProductLabel::class);
}

// Related products (both directions)
public function relatedProducts(): BelongsToMany
{
    return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
        ->withTimestamps();
}

// Images (gallery)
public function images(): HasMany
{
    return $this->hasMany(ProductImage::class);
}

// Accessor for featured image
public function getFeaturedImageAttribute(): ?string
{
    $first = $this->images()->orderBy('sort_order')->first();
    return $first?->url;
}
```

Also update `$fillable` to include new columns:
```php
protected $fillable = [
    'sku', 'name', 'slug', 'short_description', 'description',
    'price', 'compare_price', 'cost_price', 'brand_id',
    'stock_quantity', 'track_stock', 'status', 'sort_order',
    'gender', 'product_type', 'meta_title', 'meta_description',
    'focus_keyword', 'weight', 'length', 'width', 'height',
    'shipping_class', 'free_shipping', 'enable_reviews',
    'is_taxable', 'is_featured', 'published_at',
];
```

Also add `$casts` for new boolean/date fields:
```php
'free_shipping' => 'boolean',
'enable_reviews' => 'boolean',
'is_taxable' => 'boolean',
'is_featured' => 'boolean',
'published_at' => 'datetime',
```

---

## Task 2: ProductResource - Two-Column Layout & Basic Information

**Files:**
- Modify: `app/Filament/Resources/ProductResource.php`

**Steps:**

- [ ] **Step 1: Build the two-column layout shell**

Replace the entire `form()` method in ProductResource with:

```php
public static function form(Form $form): Form
{
    return $form->schema([
        Layout::Columns::make([
            // Main column (2/3)
            Column::make([
                // Basic Information section
                // ... Task 3 content
            ])->width('2/3'),

            // Sidebar column (1/3)
            Column::make([
                // Visibility, Category, Images, Tags, Related Products
                // ... Task 6 content
            ])->width('1/3'),
        ]),
    ]);
}
```

Import classes at top:
```php
use Filament\Forms\Components\Column;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Wizard;
```

- [ ] **Step 2: Basic Information Section (main column)**

In the main column, add a `Section::make('Informazioni Base')` with:

```php
Section::make('Informazioni Base')->schema([
    Grid::make(2)->schema([
        TextInput::make('sku')
            ->label('SKU')
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(50),
        Select::make('brand_id')
            ->label('Brand')
            ->relationship('brand', 'name')
            ->searchable()
            ->preload()
            ->columnSpan(1),
    ]),
    Grid::make(2)->schema([
        Select::make('gender')
            ->label('Genere')
            ->options([
                'male' => 'Uomo',
                'female' => 'Donna',
                'unisex' => 'Unisex',
                'kids' => 'Bambini',
            ])
            ->nullable(),
        Select::make('product_type')
            ->label('Tipo Prodotto')
            ->options([
                'simple' => 'Semplice',
                'variable' => 'Variabile',
                'grouped' => 'Raggruppato',
                'external' => 'Esterno/Affiliato',
            ])
            ->default('simple'),
    ]),
    TextInput::make('name')
        ->label('Nome Prodotto')
        ->required()
        ->live(onBlur: true)
        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
        ->maxLength(255),
    Hidden::make('slug'),
    Select::make('categories')
        ->label('Categorie')
        ->relationship('categories', 'name')
        ->multiple()
        ->searchable()
        ->preload(),
    Textarea::make('short_description')
        ->label('Descrizione Breve')
        ->rows(2)
        ->maxLength(500),
    RichEditor::make('description')
        ->label('Descrizione Completa')
        ->toolbarButtons([
            'bold', 'italic', 'underline', 'strike',
            'link', 'orderedList', 'bulletList',
            'blockquote', 'codeBlock', 'undo', 'redo',
        ]),
    Textarea::make('filters_data')
        ->label('Filtri')
        ->placeholder('Colore, Materiale, Stile (uno per riga)')
        ->rows(3)
        ->helperText('Inserisci i filtri applicabili al prodotto, uno per riga'),
]),
```

---

## Task 3: Pricing & Inventory Section + Variants Section

**Files:**
- Modify: `app/Filament/Resources/ProductResource.php`

Add after Basic Information in the main column:

```php
Section::make('Prezzo e Inventario')->schema([
    Grid::make(3)->schema([
        TextInput::make('price')
            ->label('Prezzo (€)')
            ->required()
            ->numeric()
            ->step(0.01)
            ->prefix('€'),
        TextInput::make('compare_price')
            ->label('Prezzo Scontato (€)')
            ->numeric()
            ->step(0.01)
            ->prefix('€')
            ->helperText('Mostrato barrato accanto al prezzo'),
        TextInput::make('cost_price')
            ->label('Costo (€)')
            ->numeric()
            ->step(0.01)
            ->prefix('€')
            ->helperText('Non mostrato ai clienti'),
    ]),
    Grid::make(2)->schema([
        TextInput::make('stock_quantity')
            ->label('Giacenza')
            ->numeric()
            ->default(0),
        TextInput::make('sku')
            ->label('SKU')
            ->required()
            ->unique(ignoreRecord: true),
    ]),
    Grid::make(3)->schema([
        Toggle::make('track_stock')
            ->label('Traccia Inventario')
            ->default(true),
        Toggle::make('is_taxable')
            ->label('Tassabile')
            ->default(true),
        Toggle::make('enable_reviews')
            ->label('Consenti Recensioni')
            ->default(true),
    ]),
]),

Section::make('Varianti Prodotto')->schema([
    Toggle::make('has_variants')
        ->label('Questo prodotto ha varianti (taglia, colore, ecc.)')
        ->default(false)
        ->reactive(),
    
    // Show variant builder only when has_variants is true
    Grid::make(2)->schema([
        // Variant Options Builder
        Section::make('Opzioni Varianti')->schema([
            TextInput::make('variant_option_name')
                ->label('Nome Opzione (es. Taglia, Colore)')
                ->placeholder('Es. Taglia')
                ->reactive(),
            Textarea::make('variant_option_values')
                ->label('Valori (uno per riga)')
                ->placeholder('S
M
L
XL')
                ->rows(5)
                ->reactive(),
            Button::make('add_variant_option')
                ->label('Aggiungi Opzione')
                ->action(function (array $data, Get $get, Set $set) {
                    $options = json_decode($get('variant_options_json') ?? '[]', true);
                    $name = $data['variant_option_name'] ?? '';
                    $values = array_filter(array_map('trim', explode("\n", $data['variant_option_values'] ?? '')));
                    if ($name && count($values)) {
                        $options[] = ['name' => $name, 'values' => array_values($values)];
                        $set('variant_options_json', json_encode($options));
                        $set('variant_option_name', '');
                        $set('variant_option_values', '');
                    }
                })
                ->disabled(fn (Get $get) => empty($get('variant_option_name'))),
            Hidden::make('variant_options_json'),
            // Display current options
            ViewField::make('variant_options_display')
                ->label('Opzioni Configurate')
                ->view('filament.forms.components.variant-options-display'),
        ])->columns(1),
        
        // Variant Generation Preview
        Section::make('Anteprima Varianti Generate')->schema([
            ViewField::make('variants_preview')
                ->view('filament.forms.components.variants-preview'),
        ]),
    ])->visible(fn (Get $get) => $get('has_variants')),
    
    // Manual variant entries
    Repeater::make('variants')
        ->label('Varianti')
        ->schema([
            TextInput::make('sku')->label('SKU Variante')->required(),
            TextInput::make('name')->label('Nome (es. Rosso - XL)')->required(),
            TextInput::make('price')->label('Prezzo')->numeric()->prefix('€'),
            TextInput::make('stock_quantity')->label('Giacenza')->numeric(),
            TextInput::make('weight')->label('Peso (g)')->numeric(),
        ])->columns(3)
        ->visible(fn (Get $get) => $get('has_variants')),
]),
```

---

## Task 4: SEO Settings & Shipping Sections

**Files:**
- Modify: `app/Filament/Resources/ProductResource.php`

Add after Variants in the main column:

```php
Section::make('SEO')->schema([
    Grid::make(2)->schema([
        TextInput::make('meta_title')
            ->label('Meta Title')
            ->maxLength(60)
            ->helperText('Titolo per i motori di ricerca (max 60 caratteri)'),
        TextInput::make('focus_keyword')
            ->label('Parola Chiave Focus')
            ->maxLength(100)
            ->helperText('Parola chiave principale'),
    ]),
    Textarea::make('meta_description')
        ->label('Meta Description')
        ->rows(3)
        ->maxLength(160)
        ->helperText('Descrizione per i motori di ricerca (max 160 caratteri)'),
    Grid::make(3)->schema([
        TextInput::make('slug')
            ->label('URL Slug')
            ->unique(ignoreRecord: true)
            ->helperText('URL del prodotto'),
        ViewField::make('seo_score')
            ->label('Score SEO')
            ->view('filament.forms.components.seo-score'),
        Placeholder::make('seo_preview')
            ->label('Anteprima Google')
            ->content(function (Get $get) {
                $title = $get('meta_title') ?: $get('name') ?: 'Titolo pagina';
                $desc = $get('meta_description') ?: 'Descrizione non ancora configurata.';
                return "{$title}\n{$desc}";
            }),
    ]),
]),

Section::make('Spedizione')->schema([
    Grid::make(4)->schema([
        TextInput::make('weight')
            ->label('Peso (kg)')
            ->numeric()
            ->step(0.001),
        TextInput::make('length')
            ->label('Lunghezza (cm)')
            ->numeric()
            ->step(0.1),
        TextInput::make('width')
            ->label('Larghezza (cm)')
            ->numeric()
            ->step(0.1),
        TextInput::make('height')
            ->label('Altezza (cm)')
            ->numeric()
            ->step(0.1),
    ]),
    Grid::make(2)->schema([
        Select::make('shipping_class')
            ->label('Classe Spedizione')
            ->options([
                'standard' => 'Standard',
                'express' => 'Express',
                'oversized' => 'Over Size',
                'fragile' => 'Fragile',
            ])
            ->nullable(),
        Toggle::make('free_shipping')
            ->label('Spedizione Gratuita')
            ->default(false),
    ]),
]),
```

---

## Task 5: Sidebar Sections

**Files:**
- Modify: `app/Filament/Resources/ProductResource.php`

In the sidebar column (1/3 width), add:

```php
Column::make([
    // Visibility Section
    Section::make('Visibilità')->schema([
        Toggle::make('status')
            ->label('Pubblicato')
            ->default(false)
            ->hintIcon('heroicon-o-information-circle')
            ->hint('Il prodotto sarà visibile nel negozio'),
        Grid::make(1)->schema([
            DateTimePicker::make('published_at')
                ->label('Pubblicazione Programmata')
                ->nullable()
                ->helperText('Lascia vuoto per pubblicare subito'),
            Select::make('visibility_status')
                ->label('Stato Visibilità')
                ->options([
                    'published' => 'Pubblicato',
                    'scheduled' => 'Programmato',
                    'hidden' => 'Nascondi',
                ])
                ->default('published'),
        ]),
    ]),

    // Product Images Section
    Section::make('Immagini Prodotto')->schema([
        FileUpload::make('images')
            ->label('Galleria Immagini')
            ->multiple()
            ->image()
            ->directory('products/images')
            ->maxSize(5120)
            ->panelLayout('grid')
            ->reorderable()
            ->appendFiles()
            ->imageCropAspectRatio('1:1')
            ->helperText('Trascina per riordinare. Prima immagine = copertina.'),
    ]),

    // Labels Section
    Section::make('Etichette')->schema([
        Grid::make(2)->schema([
            Toggle::make('labels.new_arrival')
                ->label('Nuovo Arrivo')
                ->default(false),
            Toggle::make('labels.best_seller')
                ->label('Best Seller')
                ->default(false),
            Toggle::make('labels.featured')
                ->label('In Evidenza')
                ->default(false),
            Toggle::make('labels.on_sale')
                ->label('In Sconto')
                ->default(false),
            Toggle::make('labels.limited')
                ->label('Edizione Limitata')
                ->default(false),
            Toggle::make('labels.exclusive')
                ->label('Esclusivo')
                ->default(false),
        ]),
    ]),

    // Related Products Section
    Section::make('Prodotti Correlati')->schema([
        Select::make('related_products_list')
            ->label('Seleziona Prodotti')
            ->relationship('relatedProducts', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->helperText('Prodotti mostrati come suggerimenti'),
    ]),

    // SEO Score Summary (sidebar)
    Section::make('Riepilogo SEO')->schema([
        ViewField::make('sidebar_seo_summary')
            ->view('filament.forms.components.seo-sidebar-summary'),
    ]),
])->columnSpan(1),
```

---

## Task 6: Blade View Components for Custom Fields

**Files:**
- Create: `resources/views/filament/forms/components/variant-options-display.blade.php`
- Create: `resources/views/filament/forms/components/variants-preview.blade.php`
- Create: `resources/views/filament/forms/components/seo-score.blade.php`
- Create: `resources/views/filament/forms/components/seo-sidebar-summary.blade.php`

**variant-options-display.blade.php:**
```blade
@php
    $options = json_decode($get('variant_options_json') ?? '[]', true);
@endphp
<div>
    @forelse($options as $opt)
        <div class="mb-2 p-2 bg-gray-50 rounded">
            <strong>{{ $opt['name'] }}:</strong>
            @foreach($opt['values'] as $val)
                <span class="inline-block px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs rounded mr-1">{{ $val }}</span>
            @endforeach
        </div>
    @empty
        <p class="text-sm text-gray-500">Nessuna opzione configurata</p>
    @endforelse
</div>
```

**variants-preview.blade.php:**
```blade
@php
    $options = json_decode($get('variant_options_json') ?? '[]', true);
    $combinations = [[]];
    foreach ($options as $opt) {
        $new = [];
        foreach ($combinations as $c) {
            foreach ($opt['values'] as $v) {
                $new[] = array_merge($c, [$opt['name'] => $v]);
            }
        }
        $combinations = $new;
    }
    $total = count($combinations);
@endphp
<div>
    <p class="text-sm text-gray-600">Verranno generate <strong>{{ $total }}</strong> varianti</p>
    <div class="mt-2 max-h-40 overflow-y-auto">
        @foreach(array_slice($combinations, 0, 10) as $combo)
            <div class="text-xs bg-gray-50 px-2 py-1 mb-1 rounded">
                {{ implode(' + ', array_values($combo)) }}
            </div>
        @endforeach
        @if($total > 10)
            <p class="text-xs text-gray-400">... e altre {{ $total - 10 }}</p>
        @endif
    </div>
</div>
```

**seo-score.blade.php:**
```blade
@php
    $title = $get('meta_title') ?? '';
    $desc = $get('meta_description') ?? '';
    $kw = $get('focus_keyword') ?? '';
    $score = 0;
    if (strlen($title) >= 30 && strlen($title) <= 60) $score += 25;
    if (strlen($desc) >= 120 && strlen($desc) <= 160) $score += 25;
    if ($kw && str_contains(strtolower($title), strtolower($kw))) $score += 25;
    if ($kw && str_contains(strtolower($desc), strtolower($kw))) $score += 25;
    $color = $score >= 75 ? 'green' : ($score >= 50 ? 'yellow' : 'red');
@endphp
<div class="flex items-center gap-2">
    <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="bg-{{ $color }}-500 h-2.5 rounded-full" style="width: {{ $score }}%"></div>
    </div>
    <span class="text-sm font-medium text-{{ $color }}-600">{{ $score }}/100</span>
</div>
```

**seo-sidebar-summary.blade.php:**
```blade
@php
    $title = $get('meta_title') ?: $get('name') ?: '';
    $titleLen = strlen($title);
    $desc = $get('meta_description') ?: '';
    $descLen = strlen($desc);
    $kw = $get('focus_keyword') ?: '';
@endphp
<div class="space-y-2 text-sm">
    <div class="flex justify-between">
        <span>Title</span>
        <span class="{{ $titleLen >= 30 && $titleLen <= 60 ? 'text-green-600' : 'text-yellow-600' }}">
            {{ $titleLen }}/60
        </span>
    </div>
    <div class="flex justify-between">
        <span>Description</span>
        <span class="{{ $descLen >= 120 && $descLen <= 160 ? 'text-green-600' : 'text-yellow-600' }}">
            {{ $descLen }}/160
        </span>
    </div>
    <div class="flex justify-between">
        <span>Keyword</span>
        <span class="{{ $kw ? 'text-green-600' : 'text-red-600' }}">
            {{ $kw ? '✓' : '✗' }}
        </span>
    </div>
</div>
```

---

## Task 7: Language Files

**Files:**
- Modify: `resources/lang/it/product.php` (create if not exists)

```php
<?php
return [
    'name' => 'Nome',
    'sku' => 'SKU',
    'price' => 'Prezzo',
    'stock' => 'Giacenza',
    'status' => 'Stato',
    'categories' => 'Categorie',
    'description' => 'Descrizione',
    'short_description' => 'Descrizione Breve',
    'brand' => 'Brand',
    'gender' => 'Genere',
    'product_type' => 'Tipo Prodotto',
    'published_at' => 'Data Pubblicazione',
    // ... add all field labels
];
```

---

## Verification

After all tasks, verify:
1. Migration runs: `php artisan migrate` — all 7 migrations apply cleanly
2. ProductResource form renders: two-column layout with all sections
3. Product model has all relationships
4. Blade components exist and render
5. Form saves and retrieves all data correctly