# Phase 2: Product Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the public-facing product catalog — shop listing page with filters, product detail page with variants, category browsing, and brand filtering.

**Architecture:** Livewire components for dynamic filtering and product display. Blade views for layout. Tailwind CSS for styling matching the existing homepage design (dark luxury theme).

**Tech Stack:** Livewire 3, Alpine.js, Tailwind CSS, Laravel pagination

**Prerequisites:** Phase 1 complete — all models, migrations, seeders, and Filament admin working.

---

### Task 1: Create Shop Layout

**Files:**
- Create: `resources/views/layouts/app-shop.blade.php`

- [ ] **Step 1: Create shop layout**

Create `resources/views/layouts/app-shop.blade.php` — extends the same nav/footer as app-home but with a simpler structure for catalog pages:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SweetHome Milano' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:300,400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-brand-dark text-brand-light antialiased font-sans">

    <nav x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-50 bg-brand-dark/95 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-serif tracking-widest uppercase text-brand-light">SweetHome</a>
                </div>
                <div class="hidden sm:flex sm:items-center sm:space-x-8">
                    <a href="/" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Home</a>
                    <a href="/shop" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Shop</a>
                    <a href="/services" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Servizi</a>
                    <a href="/contact" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Contatti</a>
                    <a href="/cart" class="relative group">
                        <svg class="w-6 h-6 hover:text-brand-gold transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </a>
                </div>
                <div class="sm:hidden flex items-center">
                    <button @click="open = !open" class="text-brand-light hover:text-brand-gold transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="open" x-cloak x-transition class="sm:hidden border-t border-white/10 bg-brand-dark">
            <div class="px-4 py-4 space-y-3">
                <a href="/" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Home</a>
                <a href="/shop" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Shop</a>
                <a href="/services" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Servizi</a>
                <a href="/contact" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Contatti</a>
                <a href="/cart" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Carrello</a>
            </div>
        </div>
    </nav>

    <main class="pt-20">
        @yield('content')
    </main>

    <footer class="bg-black border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="md:col-span-1">
                    <a href="/" class="text-2xl font-serif tracking-widest uppercase text-brand-light">SweetHome</a>
                    <p class="mt-4 text-sm text-gray-400 leading-relaxed">Design d'interni di lusso per chi cerca l'eccellenza nell'arredamento.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Navigazione</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-sm text-gray-400 hover:text-brand-light transition">Home</a></li>
                        <li><a href="/shop" class="text-sm text-gray-400 hover:text-brand-light transition">Shop</a></li>
                        <li><a href="/services" class="text-sm text-gray-400 hover:text-brand-light transition">Servizi</a></li>
                        <li><a href="/contact" class="text-sm text-gray-400 hover:text-brand-light transition">Contatti</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Contatti</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Via Carlo Espinasse 104/a</li>
                        <li>20156 Milano, Italia</li>
                        <li class="pt-2"><a href="tel:+393288995628" class="hover:text-brand-light transition">328 899 5628</a></li>
                        <li><a href="mailto:info@sweethome.it" class="hover:text-brand-light transition">info@sweethome.it</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Seguici</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-brand-gold transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-brand-gold transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-xs text-gray-500">&copy; {{ date('Y') }} SweetHome Milano. Tutti i diritti riservati.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition">Privacy Policy</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/app-shop.blade.php
git commit -m "feat: create shop layout with nav and footer"
```

---

### Task 2: Create Product Card Component

**Files:**
- Create: `resources/views/components/product-card.blade.php`

- [ ] **Step 1: Create product card Blade component**

Create `resources/views/components/product-card.blade.php`:
```blade
@props(['product'])

<a href="{{ route('product.show', $product->slug) }}" class="group block">
    <div class="relative overflow-hidden rounded-sm bg-brand-slate/10">
        <div class="aspect-square overflow-hidden">
            @if($product->hasMedia('images'))
                <img src="{{ $product->getFirstMediaUrl('images', 'medium') }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full bg-brand-slate/20 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                    </svg>
                </div>
            @endif
        </div>
        @if($product->compare_price && $product->compare_price > $product->price)
            <span class="absolute top-3 left-3 bg-brand-gold text-brand-dark text-xs font-bold px-2 py-1 rounded-sm">
                -{{ round((1 - $product->price / $product->compare_price) * 100) }}%
            </span>
        @endif
    </div>
    <div class="mt-4">
        @if($product->brand)
            <p class="text-xs text-gray-500 tracking-wider uppercase">{{ $product->brand->name }}</p>
        @endif
        <h3 class="text-base font-serif text-brand-light group-hover:text-brand-gold transition-colors mt-1">
            {{ $product->name }}
        </h3>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-lg font-medium text-brand-gold">
                €{{ number_format($product->price, 2, ',', '.') }}
            </span>
            @if($product->compare_price && $product->compare_price > $product->price)
                <span class="text-sm text-gray-500 line-through">
                    €{{ number_format($product->compare_price, 2, ',', '.') }}
                </span>
            @endif
        </div>
    </div>
</a>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/product-card.blade.php
git commit -m "feat: create product card Blade component"
```

---

### Task 3: Create Shop Page Livewire Component

**Files:**
- Create: `app/Http/Livewire/ShopPage.php`
- Create: `resources/views/livewire/shop-page.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create ShopPage Livewire component**

Create `app/Http/Livewire/ShopPage.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ShopPage extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $brand = '';
    public $sort = 'newest';
    public $minPrice = '';
    public $maxPrice = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'brand' => ['except' => ''],
        'sort' => ['except' => 'newest'],
        'minPrice' => ['except' => ''],
        'maxPrice' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingBrand()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'brand', 'sort', 'minPrice', 'maxPrice']);
    }

    public function render()
    {
        $query = Product::query()->published();

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('short_description', 'like', '%' . $this->search . '%');
            });
        }

        // Category filter
        if ($this->category) {
            $category = Category::where('slug', $this->category)->first();
            if ($category) {
                $categoryIds = [$category->id];
                $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            }
        }

        // Brand filter
        if ($this->brand) {
            $query->where('brand_id', $this->brand);
        }

        // Price filter
        if ($this->minPrice) {
            $query->where('price', '>=', $this->minPrice);
        }
        if ($this->maxPrice) {
            $query->where('price', '<=', $this->maxPrice);
        }

        // Sorting
        match ($this->sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);

        $categories = Category::whereNull('parent_id')->where('active', true)->orderBy('sort_order')->get();
        $brands = Brand::where('active', true)->orderBy('sort_order')->get();

        $currentCategory = $this->category ? Category::where('slug', $this->category)->first() : null;

        return view('livewire.shop-page', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'currentCategory' => $currentCategory,
        ]);
    }
}
```

- [ ] **Step 2: Create shop page view**

Create `resources/views/livewire/shop-page.blade.php`:
```blade
<div>
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex text-sm text-gray-400">
            <a href="/" class="hover:text-brand-gold transition">Home</a>
            <span class="mx-2">/</span>
            @if($currentCategory)
                @foreach($currentCategory->breadcrumb() as $cat)
                    <a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-brand-gold transition">{{ $cat->name }}</a>
                    @if(!$loop->last)<span class="mx-2">/</span>@endif
                @endforeach
            @else
                <span class="text-brand-light">Shop</span>
            @endif
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                {{ $currentCategory ? $currentCategory->name : 'Tutti i Prodotti' }}
            </h1>
            @if($currentCategory?->description)
                <p class="mt-2 text-gray-400">{{ $currentCategory->description }}</p>
            @endif
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="sticky top-24 space-y-8">
                    <!-- Search -->
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Cerca</h3>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Cerca prodotti..."
                               class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                    </div>

                    <!-- Categories -->
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Categorie</h3>
                        <ul class="space-y-2">
                            <li>
                                <button wire:click="$set('category', '')"
                                        class="text-sm {{ !$category ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                    Tutte
                                </button>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <button wire:click="$set('category', '{{ $cat->slug }}')"
                                            class="text-sm {{ $category === $cat->slug ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                        {{ $cat->name }}
                                    </button>
                                    @if($cat->children->count() > 0 && $category === $cat->slug)
                                        <ul class="ml-4 mt-1 space-y-1">
                                            @foreach($cat->children as $child)
                                                <li>
                                                    <button wire:click="$set('category', '{{ $child->slug }}')"
                                                            class="text-xs {{ $category === $child->slug ? 'text-brand-gold font-semibold' : 'text-gray-500 hover:text-brand-light' }} transition">
                                                        {{ $child->name }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Brands -->
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Marchi</h3>
                        <ul class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($brands as $brand)
                                <li>
                                    <button wire:click="$set('brand', {{ $brand->id }})"
                                            class="text-sm {{ $brand == $brand ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                        {{ $brand->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Prezzo</h3>
                        <div class="flex gap-2">
                            <input type="number" wire:model.live.debounce.300ms="minPrice"
                                   placeholder="Min"
                                   class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            <input type="number" wire:model.live.debounce.300ms="maxPrice"
                                   placeholder="Max"
                                   class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    @if($search || $category || $brand || $minPrice || $maxPrice)
                        <button wire:click="clearFilters"
                                class="text-sm text-brand-gold hover:text-brand-light transition underline">
                            Cancella filtri
                        </button>
                    @endif
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <!-- Sort & Count -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-400">
                        {{ $products->total() }} prodotti trovati
                    </p>
                    <select wire:model.live="sort"
                            class="px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none cursor-pointer">
                        <option value="newest">Più recenti</option>
                        <option value="price_asc">Prezzo: dal più economico</option>
                        <option value="price_desc">Prezzo: dal più caro</option>
                        <option value="name_asc">Nome: A-Z</option>
                        <option value="name_desc">Nome: Z-A</option>
                    </select>
                </div>

                <!-- Products -->
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-20">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <h3 class="text-xl font-serif text-brand-light mb-2">Nessun prodotto trovato</h3>
                        <p class="text-gray-400 mb-6">Prova a modificare i filtri di ricerca.</p>
                        <button wire:click="clearFilters"
                                class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                            Cancella filtri
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Add routes**

Modify `routes/web.php` — add shop routes:
```php
use App\Http\Livewire\ShopPage;
use App\Http\Livewire\ProductDetail;

Route::get('/shop', ShopPage::class)->name('shop');
Route::get('/shop/{category}', ShopPage::class)->name('shop.category');
Route::get('/shop/{category}/{product}', ProductDetail::class)->name('product.show');
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/ShopPage.php resources/views/livewire/shop-page.blade.php routes/web.php
git commit -m "feat: create shop page with filters and pagination"
```

---

### Task 4: Create Product Detail Livewire Component

**Files:**
- Create: `app/Http/Livewire/ProductDetail.php`
- Create: `resources/views/livewire/product-detail.blade.php`

- [ ] **Step 1: Create ProductDetail Livewire component**

Create `app/Http/Livewire/ProductDetail.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public $categorySlug;
    public $productSlug;
    public $selectedAttributes = [];
    public $quantity = 1;
    public $activeImageIndex = 0;

    public function mount($category, $product)
    {
        $this->categorySlug = $category;
        $this->productSlug = $product;
    }

    public function addToCart()
    {
        $product = Product::where('slug', $this->productSlug)->firstOrFail();

        $cart = session()->get('cart', []);
        $cartKey = $product->id . '-' . md5(json_encode($this->selectedAttributes));

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $this->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->getFirstMediaUrl('images', 'thumb'),
                'attributes' => $this->selectedAttributes,
                'quantity' => $this->quantity,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Prodotto aggiunto al carrello!');
    }

    public function render()
    {
        $product = Product::where('slug', $this->productSlug)
            ->published()
            ->with(['brand', 'categories', 'attributes', 'reviews' => fn($q) => $q->approved()])
            ->firstOrFail();

        $relatedProducts = Product::published()
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('category_id', $product->categories->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('livewire.product-detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
```

- [ ] **Step 2: Create product detail view**

Create `resources/views/livewire/product-detail.blade.php`:
```blade
<div>
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex text-sm text-gray-400">
            <a href="/" class="hover:text-brand-gold transition">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop') }}" class="hover:text-brand-gold transition">Shop</a>
            @foreach($product->categories->take(2) as $cat)
                <span class="mx-2">/</span>
                <a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-brand-gold transition">{{ $cat->name }}</a>
            @endforeach
            <span class="mx-2">/</span>
            <span class="text-brand-light">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Image Gallery -->
            <div>
                <div class="aspect-square overflow-hidden rounded-sm bg-brand-slate/10 mb-4">
                    @if($product->hasMedia('images'))
                        <img src="{{ $product->getMedia('images')[$activeImageIndex]->getUrl('medium') }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                @if($product->hasMedia('images') && $product->getMedia('images')->count() > 1)
                    <div class="flex gap-3 overflow-x-auto">
                        @foreach($product->getMedia('images') as $index => $image)
                            <button wire:click="$set('activeImageIndex', {{ $index }})"
                                    class="flex-shrink-0 w-20 h-20 rounded-sm overflow-hidden border-2 {{ $activeImageIndex === $index ? 'border-brand-gold' : 'border-transparent' }} transition">
                                <img src="{{ $image->getUrl('thumb') }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div>
                @if($product->brand)
                    <p class="text-sm text-brand-gold tracking-wider uppercase mb-2">{{ $product->brand->name }}</p>
                @endif
                <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-4">{{ $product->name }}</h1>

                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl font-medium text-brand-gold">
                        €{{ number_format($product->price, 2, ',', '.') }}
                    </span>
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <span class="text-lg text-gray-500 line-through">
                            €{{ number_format($product->compare_price, 2, ',', '.') }}
                        </span>
                    @endif
                </div>

                <p class="text-gray-400 mb-6">{{ $product->short_description }}</p>

                <p class="text-xs text-gray-500 mb-6">SKU: {{ $product->sku }}</p>

                <!-- Attribute Selectors -->
                @if($product->attributes->count() > 0)
                    <div class="space-y-4 mb-8">
                        @foreach($product->attributes as $attribute)
                            <div>
                                <label class="text-sm text-gray-400 mb-2 block">{{ $attribute->name }}</label>
                                <select wire:model.live="selectedAttributes.{{ $attribute->name }}"
                                        class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none cursor-pointer">
                                    <option value="">Seleziona...</option>
                                    @foreach($attribute->values as $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        @if(array_filter($this->selectedAttributes))
                            <button wire:click="$set('selectedAttributes', [])"
                                    class="text-xs text-brand-gold hover:text-brand-light transition underline">
                                Svuota selezione
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Quantity & Add to Cart -->
                <div class="flex gap-4 mb-8">
                    <div class="flex items-center border border-white/10 rounded-sm">
                        <button wire:click="if($quantity > 1) $quantity--"
                                class="px-4 py-3 text-brand-light hover:text-brand-gold transition">−</button>
                        <span class="px-4 py-3 text-brand-light min-w-[3rem] text-center">{{ $quantity }}</span>
                        <button wire:click="$quantity++"
                                class="px-4 py-3 text-brand-light hover:text-brand-gold transition">+</button>
                    </div>
                    <button wire:click="addToCart"
                            class="flex-1 px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                        Aggiungi al Carrello
                    </button>
                </div>

                <!-- Stock Info -->
                @if($product->track_stock)
                    <p class="text-sm {{ $product->stock_quantity > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $product->stock_quantity > 0 ? 'Disponibile (' . $product->stock_quantity . ' pezzi)' : 'Non disponibile' }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Tabs: Description, Info, Reviews -->
        <div class="mt-16" x-data="{ tab: 'description' }">
            <div class="border-b border-white/10">
                <nav class="flex gap-8">
                    <button @click="tab = 'description'"
                            :class="tab === 'description' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Descrizione
                    </button>
                    <button @click="tab = 'info'"
                            :class="tab === 'info' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Informazioni Aggiuntive
                    </button>
                    <button @click="tab = 'reviews'"
                            :class="tab === 'reviews' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Recensioni ({{ $product->reviews->count() }})
                    </button>
                </nav>
            </div>

            <div class="py-8">
                <div x-show="tab === 'description'" class="prose prose-invert max-w-none">
                    {!! $product->description !!}
                </div>

                <div x-show="tab === 'info'" x-cloak>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">SKU</dt>
                            <dd class="text-brand-light">{{ $product->sku }}</dd>
                        </div>
                        @if($product->brand)
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">Marca</dt>
                            <dd class="text-brand-light">{{ $product->brand->name }}</dd>
                        </div>
                        @endif
                        @foreach($product->attributes as $attr)
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">{{ $attr->name }}</dt>
                            <dd class="text-brand-light">{{ implode(', ', $attr->values) }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>

                <div x-show="tab === 'reviews'" x-cloak>
                    @if($product->reviews->count() > 0)
                        <div class="space-y-6">
                            @foreach($product->reviews as $review)
                                <div class="border-b border-white/5 pb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="flex text-brand-gold">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-600' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-sm text-gray-400">{{ $review->user_name }}</span>
                                        <span class="text-xs text-gray-600">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-sm text-gray-300">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400">Nessuna recensione ancora. Sii il primo!</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-serif font-bold text-brand-light mb-8">Prodotti Correlati</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Livewire/ProductDetail.php resources/views/livewire/product-detail.blade.php
git commit -m "feat: create product detail page with gallery, attributes, tabs, related products"
```

---

### Task 5: Create Cart Page Livewire Component

**Files:**
- Create: `app/Http/Livewire/CartPage.php`
- Create: `resources/views/livewire/cart-page.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create CartPage Livewire component**

Create `app/Http/Livewire/CartPage.php`:
```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CartPage extends Component
{
    public $cart = [];

    protected $listeners = ['cart-updated' => 'refreshCart'];

    public function mount()
    {
        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->cart = session()->get('cart', []);
    }

    public function updateQuantity($key, $quantity)
    {
        if ($quantity < 1) {
            $this->removeItem($key);
            return;
        }

        $this->cart[$key]['quantity'] = $quantity;
        session()->put('cart', $this->cart);
    }

    public function removeItem($key)
    {
        unset($this->cart[$key]);
        session()->put('cart', $this->cart);
    }

    public function clearCart()
    {
        session()->forget('cart');
        $this->cart = [];
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
```

- [ ] **Step 2: Create cart page view**

Create `resources/views/livewire/cart-page.blade.php`:
```blade
<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Carrello</h1>

        @if(count($cart) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cart as $key => $item)
                        <div class="flex gap-4 p-4 bg-brand-slate/10 rounded-sm border border-white/5">
                            <div class="w-24 h-24 flex-shrink-0 rounded-sm overflow-hidden bg-brand-slate/20">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-serif text-brand-light">{{ $item['name'] }}</h3>
                                @if(!empty($item['attributes']) && array_filter($item['attributes']))
                                    <p class="text-xs text-gray-500 mt-1">
                                        @foreach($item['attributes'] as $name => $value)
                                            {{ $name }}: {{ $value }}@if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                @endif
                                <p class="text-brand-gold mt-2">€{{ number_format($item['price'], 2, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <button wire:click="removeItem('{{ $key }}')"
                                        class="text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div class="flex items-center border border-white/10 rounded-sm">
                                    <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                            class="px-3 py-1 text-brand-light hover:text-brand-gold transition text-sm">−</button>
                                    <span class="px-3 py-1 text-brand-light text-sm min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                            class="px-3 py-1 text-brand-light hover:text-brand-gold transition text-sm">+</button>
                                </div>
                                <p class="text-sm text-brand-gold font-medium">
                                    €{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-between pt-4">
                        <button wire:click="clearCart"
                                class="text-sm text-gray-400 hover:text-red-400 transition underline">
                            Svuota carrello
                        </button>
                        <a href="{{ route('shop') }}"
                           class="text-sm text-brand-gold hover:text-brand-light transition">
                            ← Continua lo shopping
                        </a>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 sticky top-24">
                        <h2 class="text-lg font-serif text-brand-light mb-6">Riepilogo Ordine</h2>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Subtotale</span>
                                <span class="text-brand-light">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Spedizione</span>
                                <span class="text-brand-light">Calcolata al checkout</span>
                            </div>
                        </div>

                        <div class="border-t border-white/10 pt-4 mb-6">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-brand-light">Totale</span>
                                <span class="text-xl font-bold text-brand-gold">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout') }}"
                           class="block w-full text-center px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                            Procedi al Checkout
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-20">
                <svg class="w-20 h-20 mx-auto text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <h3 class="text-xl font-serif text-brand-light mb-2">Il tuo carrello è vuoto</h3>
                <p class="text-gray-400 mb-8">Aggiungi qualche prodotto per iniziare.</p>
                <a href="{{ route('shop') }}"
                   class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                    Vai allo Shop
                </a>
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 3: Add cart route**

Modify `routes/web.php`:
```php
use App\Http\Livewire\CartPage;

Route::get('/cart', CartPage::class)->name('cart');
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/CartPage.php resources/views/livewire/cart-page.blade.php routes/web.php
git commit -m "feat: create cart page with session-based cart"
```

---

### Task 6: Create Checkout Livewire Component

**Files:**
- Create: `app/Http/Livewire/CheckoutPage.php`
- Create: `resources/views/livewire/checkout-page.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create CheckoutPage Livewire component**

Create `app/Http/Livewire/CheckoutPage.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;

class CheckoutPage extends Component
{
    public $step = 1;

    // Customer info
    public $customer_name = '';
    public $customer_email = '';
    public $customer_phone = '';

    // Shipping address
    public $address_line1 = '';
    public $address_line2 = '';
    public $city = '';
    public $province = '';
    public $postal_code = '';
    public $country = 'IT';

    // Payment
    public $payment_method = 'stripe';

    public $cart = [];

    protected $rules = [
        'customer_name' => 'required|string|min:2|max:255',
        'customer_email' => 'required|email|max:255',
        'customer_phone' => 'nullable|string|max:20',
        'address_line1' => 'required|string|max:255',
        'address_line2' => 'nullable|string|max:255',
        'city' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'postal_code' => 'required|string|max:10',
        'country' => 'required|string|max:2',
        'payment_method' => 'required|in:stripe,paypal,bank_transfer',
    ];

    public function mount()
    {
        $this->cart = session()->get('cart', []);

        if (empty($this->cart)) {
            redirect()->route('cart');
        }

        if (auth()->check()) {
            $this->customer_name = auth()->user()->name;
            $this->customer_email = auth()->user()->email;
            $this->customer_phone = auth()->user()->phone ?? '';
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateOnly('customer_name');
            $this->validateOnly('customer_email');
        }
        if ($this->step === 2) {
            $this->validateOnly('address_line1');
            $this->validateOnly('city');
            $this->validateOnly('province');
            $this->validateOnly('postal_code');
        }
        $this->step++;
    }

    public function placeOrder()
    {
        $this->validate();

        $order = Order::create([
            'order_number' => 'SH-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id' => auth()->id(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'shipping_address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
            ],
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shippingCost,
            'tax' => 0,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'payment_status' => 'pending',
        ]);

        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku' => '',
                'attribute_selection' => $item['attributes'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        session()->forget('cart');

        redirect()->route('checkout.success', $order->order_number);
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function getShippingCostProperty()
    {
        $threshold = \App\Models\Setting::get('free_shipping_threshold', 150);
        return $this->subtotal >= $threshold ? 0 : (\App\Models\Setting::get('shipping_cost', 9.90));
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->shippingCost;
    }

    public function render()
    {
        return view('livewire.checkout-page');
    }
}
```

- [ ] **Step 2: Create checkout page view**

Create `resources/views/livewire/checkout-page.blade.php`:
```blade
<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Checkout</h1>

        <!-- Progress Steps -->
        <div class="flex items-center mb-12">
            <div class="flex items-center {{ $step >= 1 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold
                    {{ $step >= 1 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">
                    1
                </span>
                <span class="ml-2 text-sm hidden sm:inline">Dati</span>
            </div>
            <div class="flex-1 h-px mx-4 {{ $step >= 2 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
            <div class="flex items-center {{ $step >= 2 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold
                    {{ $step >= 2 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">
                    2
                </span>
                <span class="ml-2 text-sm hidden sm:inline">Spedizione</span>
            </div>
            <div class="flex-1 h-px mx-4 {{ $step >= 3 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
            <div class="flex items-center {{ $step >= 3 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold
                    {{ $step >= 3 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">
                    3
                </span>
                <span class="ml-2 text-sm hidden sm:inline">Pagamento</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="lg:col-span-2">
                <!-- Step 1: Customer Info -->
                @if($step === 1)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Informazioni Cliente</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Nome e Cognome *</label>
                                <input type="text" wire:model="customer_name"
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_name') border-red-500 @enderror">
                                @error('customer_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Email *</label>
                                    <input type="email" wire:model="customer_email"
                                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_email') border-red-500 @enderror">
                                    @error('customer_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Telefono</label>
                                    <input type="tel" wire:model="customer_phone"
                                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
                                </div>
                            </div>
                        </div>
                        <button wire:click="nextStep"
                                class="mt-6 w-full px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                            Continua
                        </button>
                    </div>
                @endif

                <!-- Step 2: Shipping Address -->
                @if($step === 2)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Indirizzo di Spedizione</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Indirizzo *</label>
                                <input type="text" wire:model="address_line1"
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('address_line1') border-red-500 @enderror">
                                @error('address_line1')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Indirizzo (opzionale)</label>
                                <input type="text" wire:model="address_line2"
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Città *</label>
                                    <input type="text" wire:model="city"
                                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('city') border-red-500 @enderror">
                                    @error('city')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Provincia *</label>
                                    <input type="text" wire:model="province"
                                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('province') border-red-500 @enderror">
                                    @error('province')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">CAP *</label>
                                    <input type="text" wire:model="postal_code"
                                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('postal_code') border-red-500 @enderror">
                                    @error('postal_code')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button wire:click="$set('step', 1)"
                                    class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">
                                Indietro
                            </button>
                            <button wire:click="nextStep"
                                    class="flex-1 px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                                Continua
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Step 3: Payment -->
                @if($step === 3)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Metodo di Pagamento</h2>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition
                                {{ $payment_method === 'stripe' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="stripe" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Carta di Credito (Stripe)</span>
                                    <p class="text-xs text-gray-500">Visa, Mastercard, American Express</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition
                                {{ $payment_method === 'paypal' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="paypal" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">PayPal</span>
                                    <p class="text-xs text-gray-500">Paga con il tuo account PayPal</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition
                                {{ $payment_method === 'bank_transfer' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="bank_transfer" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Bonifico Bancario</span>
                                    <p class="text-xs text-gray-500">Pagamento anticipato tramite bonifico</p>
                                </div>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button wire:click="$set('step', 2)"
                                    class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">
                                Indietro
                            </button>
                            <button wire:click="placeOrder"
                                    class="flex-1 px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                                Conferma Ordine
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 sticky top-24">
                    <h2 class="text-lg font-serif text-brand-light mb-6">Il Tuo Ordine</h2>
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach($cart as $item)
                            <div class="flex justify-between text-sm">
                                <div>
                                    <span class="text-brand-light">{{ $item['name'] }}</span>
                                    <span class="text-gray-500"> × {{ $item['quantity'] }}</span>
                                </div>
                                <span class="text-brand-gold">€{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-white/10 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Subtotale</span>
                            <span class="text-brand-light">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Spedizione</span>
                            <span class="text-brand-light">
                                @if($this->shippingCost == 0)
                                    <span class="text-green-400">Gratuita</span>
                                @else
                                    €{{ number_format($this->shippingCost, 2, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        <div class="border-t border-white/10 pt-3 flex justify-between">
                            <span class="text-base font-semibold text-brand-light">Totale</span>
                            <span class="text-xl font-bold text-brand-gold">€{{ number_format($this->total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Add checkout routes**

Modify `routes/web.php`:
```php
use App\Http\Livewire\CheckoutPage;

Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/checkout/success/{orderNumber}', function ($orderNumber) {
    $order = \App\Models\Order::where('order_number', $orderNumber)->firstOrFail();
    return view('checkout-success', ['order' => $order]);
})->name('checkout.success');
```

- [ ] **Step 4: Create checkout success view**

Create `resources/views/checkout-success.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-500/20 flex items-center justify-center">
        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-3xl font-serif font-bold text-brand-light mb-4">Ordine Confermato!</h1>
    <p class="text-gray-400 mb-2">Grazie per il tuo acquisto, {{ $order->customer_name }}.</p>
    <p class="text-gray-400 mb-8">Numero ordine: <span class="text-brand-gold font-mono">{{ $order->order_number }}</span></p>
    <p class="text-sm text-gray-500 mb-8">Riceverai una email di conferma a {{ $order->customer_email }}.</p>
    <a href="{{ route('shop') }}"
       class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
        Continua lo Shopping
    </a>
</div>
@endsection
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/CheckoutPage.php resources/views/livewire/checkout-page.blade.php resources/views/checkout-success.blade.php routes/web.php
git commit -m "feat: create checkout flow with 3-step form and order creation"
```

---

### Task 7: Update Homepage to Use Shop Layout

**Files:**
- Modify: `resources/views/home.blade.php`
- Modify: `resources/views/layouts/app-home.blade.php`

- [ ] **Step 1: Update homepage to extend app-shop layout**

Modify `resources/views/home.blade.php` — change `@extends('layouts.app-home')` to `@extends('layouts.app-shop')`

- [ ] **Step 2: Commit**

```bash
git add resources/views/home.blade.php
git commit -m "chore: update homepage to use shop layout"
```

---

## Phase 2 Summary

After completing this phase you will have:
- Shop listing page with search, category/brand/price filters, sorting, and pagination
- Product detail page with image gallery, attribute selectors, quantity picker, tabs (description/info/reviews), and related products
- Shopping cart with session-based storage, quantity updates, and item removal
- 3-step checkout flow (customer info → shipping → payment) with order creation
- Order confirmation page
- All pages styled with the dark luxury theme matching the homepage
