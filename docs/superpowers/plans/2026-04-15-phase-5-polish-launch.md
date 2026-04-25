# Phase 5: Polish & Launch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Final polish — SEO optimization (meta tags, sitemap, robots.txt, Open Graph), performance improvements (image lazy loading, caching), 404 page, legal pages (privacy, cookie, terms), and pre-launch testing.

**Architecture:** SEO via spatie/laravel-sitemap package and manual meta tag injection. Caching via Laravel's cache layer. Static legal pages. Custom error pages matching the dark luxury theme.

**Tech Stack:** Laravel caching, spatie/laravel-sitemap, Blade, Tailwind CSS

**Prerequisites:** Phases 1-4 complete. All pages, models, and admin functionality working.

---

### Task 1: SEO Meta Tags & Open Graph

**Files:**
- Create: `app/View/Components/SeoMeta.php`
- Modify: `resources/views/layouts/app-shop.blade.php`
- Modify: `resources/views/layouts/app-home.blade.php`

- [ ] **Step 1: Create SeoMeta view component**

Create `app/View/Components/SeoMeta.php`:
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SeoMeta extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $url = null,
        public string $type = 'website',
    ) {}

    public function render()
    {
        return view('components.seo-meta');
    }
}
```

- [ ] **Step 2: Create seo-meta Blade view**

Create `resources/views/components/seo-meta.blade.php`:
```blade
@php
    $siteName = \App\Models\Setting::get('site_name', 'SweetHome Milano');
    $defaultDescription = 'SweetHome Milano — Materiali di pregio per la tua casa. Pavimenti, rivestimenti, bagno, rubinetteria e molto altro. Showroom a Milano.';
    $defaultImage = asset('images/og-default.jpg');
    $currentUrl = $url ?? url()->current();

    $title = $title ? "{$title} — {$siteName}" : $siteName;
    $description = $description ?? $defaultDescription;
    $image = $image ?? $defaultImage;
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $currentUrl }}">

<!-- Open Graph -->
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:url" content="{{ $currentUrl }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:locale" content="it_IT">
<meta property="og:site_name" content="{{ $siteName }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
```

- [ ] **Step 3: Update shop layout to use SeoMeta**

Modify `resources/views/layouts/app-shop.blade.php` — replace the `<title>` tag in the `<head>` with:
```blade
<x-seo-meta :title="$title ?? null" :description="$description ?? null" :image="$image ?? null" />
```

- [ ] **Step 4: Update homepage to pass SEO data**

Modify `resources/views/home.blade.php` — at the top, before `@extends`:
```blade
@php
    $title = 'SweetHome Milano — Materiali di Pregio per la Tua Casa';
    $description = 'Showroom a Milano per pavimenti, rivestimenti, bagno, rubinetteria e molto altro. Consulenza personalizzata e progettazione 3D.';
@endphp
```

- [ ] **Step 5: Update shop page to pass SEO data**

Modify `resources/views/livewire/shop-page.blade.php` — at the top:
```blade
@php
    $title = $currentCategory ? $currentCategory->name : 'Shop';
    $description = $currentCategory?->description ?? 'Scopri il nostro catalogo di materiali di pregio per la casa.';
@endphp
```

- [ ] **Step 6: Update product detail to pass SEO data**

Modify `resources/views/livewire/product-detail.blade.php` — at the top:
```blade
@php
    $title = $product->name;
    $description = $product->short_description ?? $product->name;
    $image = $product->hasMedia('images') ? $product->getFirstMediaUrl('images', 'medium') : null;
@endphp
```

- [ ] **Step 7: Commit**

```bash
git add app/View/Components/SeoMeta.php resources/views/components/seo-meta.blade.php resources/views/layouts/app-shop.blade.php resources/views/layouts/app-home.blade.php resources/views/home.blade.php
git commit -m "feat: add SEO meta tags component with Open Graph and Twitter Card support"
```

---

### Task 2: Sitemap & Robots

**Files:**
- Create: `routes/seo.php` (or add to web.php)
- Create: `public/robots.txt`

- [ ] **Step 1: Install sitemap package**

Run:
```bash
composer require spatie/laravel-sitemap
```

- [ ] **Step 2: Create sitemap generation command**

Run:
```bash
php artisan make:command GenerateSitemap
```

Create `app/Console/Commands/GenerateSitemap.php`:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Category;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.xml';

    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create('/shop')->setPriority(0.9))
            ->add(Url::create('/services')->setPriority(0.7))
            ->add(Url::create('/contact')->setPriority(0.7))
            ->add(Url::create('/projects')->setPriority(0.6));

        // Add categories
        Category::where('active', true)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create("/shop/{$category->slug}")->setPriority(0.8));
        });

        // Add products
        Product::published()->get()->each(function (Product $product) use ($sitemap) {
            $category = $product->categories->first();
            $categorySlug = $category ? $category->slug : 'shop';
            $sitemap->add(
                Url::create("/shop/{$categorySlug}/{$product->slug}")
                    ->setPriority(0.8)
                    ->setLastModificationDate($product->updated_at)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}
```

- [ ] **Step 3: Create robots.txt**

Create `public/robots.txt`:
```
User-agent: *
Disallow: /admin
Disallow: /cart
Disallow: /checkout
Disallow: /account
Disallow: /webhooks/

Sitemap: https://sweethome.it/sitemap.xml
```

- [ ] **Step 4: Add sitemap route**

Modify `routes/web.php`:
```php
Route::get('/sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'));
})->name('sitemap');
```

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/GenerateSitemap.php public/robots.txt routes/web.php composer.json composer.lock
git commit -m "feat: add sitemap generation command and robots.txt"
```

---

### Task 3: Custom Error Pages

**Files:**
- Create: `resources/views/errors/404.blade.php`
- Create: `resources/views/errors/500.blade.php`

- [ ] **Step 1: Create 404 page**

Create `resources/views/errors/404.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-8xl md:text-9xl font-serif font-bold text-brand-gold mb-4">404</h1>
        <h2 class="text-2xl md:text-3xl font-serif text-brand-light mb-4">Pagina Non Trovata</h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">
            La pagina che stai cercando non esiste o è stata spostata.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/"
               class="px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                Torna alla Home
            </a>
            <a href="{{ route('shop') }}"
               class="px-8 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">
                Vai allo Shop
            </a>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Create 500 page**

Create `resources/views/errors/500.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-8xl md:text-9xl font-serif font-bold text-brand-gold mb-4">500</h1>
        <h2 class="text-2xl md:text-3xl font-serif text-brand-light mb-4">Errore del Server</h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">
            Qualcosa è andato storto. Stiamo lavorando per risolvere il problema.
        </p>
        <a href="/"
           class="px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
            Torna alla Home
        </a>
    </div>
</div>
@endsection
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/errors/
git commit -m "feat: add custom 404 and 500 error pages"
```

---

### Task 4: Legal Pages (Privacy, Cookie, Terms)

**Files:**
- Create: `resources/views/legal/privacy.blade.php`
- Create: `resources/views/legal/cookies.blade.php`
- Create: `resources/views/legal/terms.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app-shop.blade.php` (footer links)

- [ ] **Step 1: Create privacy policy page**

Create `resources/views/legal/privacy.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-serif font-bold text-brand-light mb-8">Privacy Policy</h1>

    <div class="prose prose-invert max-w-none text-gray-400">
        <p class="text-sm text-gray-500 mb-8">Ultimo aggiornamento: {{ date('d/m/Y') }}</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">1. Titolare del Trattamento</h2>
        <p>SweetHome Milano<br>
        Via Carlo Espinasse 104/a, 20156 Milano MI<br>
        Email: info@sweethome.it<br>
        P.IVA: [Partita IVA]</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">2. Dati Personali Raccolti</h2>
        <p>I dati personali raccolti includono: nome, email, telefono, indirizzo di spedizione, e dati di navigazione. I dati sono trattati per finalità di esecuzione del contratto, adempimenti legali, e miglioramento del servizio.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">3. Finalità del Trattamento</h2>
        <p>I dati sono utilizzati per: gestione ordini, spedizione prodotti, assistenza clienti, invio newsletter (con consenso), adempimenti fiscali e legali.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">4. Base Giuridica</h2>
        <p>Il trattamento è basato su: esecuzione del contratto, consenso dell'interessato, obbligo legale, legittimo interesse del titolare.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">5. Conservazione dei Dati</h2>
        <p>I dati sono conservati per il tempo necessario all'esecuzione delle finalità indicate e nel rispetto dei termini di legge.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">6. Diritti dell'Interessato</h2>
        <p>Ai sensi del GDPR (Regolamento UE 2016/679), l'interessato ha diritto di: accesso, rettifica, cancellazione, limitazione del trattamento, portabilità dei dati, opposizione. Per esercitare i diritti, contattare: info@sweethome.it</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">7. Cookie</h2>
        <p>Questo sito utilizza cookie tecnici e, con il tuo consenso, cookie di profilazione. Vedi la nostra <a href="{{ route('legal.cookies') }}" class="text-brand-gold hover:underline">Cookie Policy</a> per maggiori dettagli.</p>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Create cookie policy page**

Create `resources/views/legal/cookies.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-serif font-bold text-brand-light mb-8">Cookie Policy</h1>

    <div class="prose prose-invert max-w-none text-gray-400">
        <p class="text-sm text-gray-500 mb-8">Ultimo aggiornamento: {{ date('d/m/Y') }}</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">Cosa sono i Cookie</h2>
        <p>I cookie sono piccoli file di testo che i siti visitati dall'utente inviano al suo dispositivo per essere memorizzati e ritrasmessi allo stesso sito in occasione di visite successive.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">Cookie Tecnici</h2>
        <p>Questo sito utilizza cookie tecnici necessari per il funzionamento del sito (es. sessione carrello, autenticazione). Questi cookie non richiedono il consenso dell'utente.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">Cookie di Terze Parti</h2>
        <p>Il sito può includere cookie di terze parti (es. Stripe, PayPal, Google Analytics) per funzionalità di pagamento e analisi del traffico. Le terze parti gestiscono autonomamente i propri cookie.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">Gestione dei Cookie</h2>
        <p>L'utente può gestire le preferenze sui cookie direttamente dal browser. La disabilitazione dei cookie tecnici può compromettere il funzionamento del sito.</p>
    </div>
</div>
@endsection
```

- [ ] **Step 3: Create terms and conditions page**

Create `resources/views/legal/terms.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-serif font-bold text-brand-light mb-8">Termini e Condizioni</h1>

    <div class="prose prose-invert max-w-none text-gray-400">
        <p class="text-sm text-gray-500 mb-8">Ultimo aggiornamento: {{ date('d/m/Y') }}</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">1. Informazioni Generali</h2>
        <p>Il sito sweethome.it è gestito da SweetHome Milano, con sede in Via Carlo Espinasse 104/a, 20156 Milano MI. P.IVA: [Partita IVA].</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">2. Prodotti e Prezzi</h2>
        <p>I prezzi sono espressi in Euro (EUR) e includono IVA. SweetHome si riserva il diritto di modificare i prezzi senza preavviso. Le immagini dei prodotti sono indicative e possono differire leggermente dal prodotto reale.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">3. Ordini</h2>
        <p>Gli ordini effettuati tramite il sito sono vincolanti. SweetHome si riserva il diritto di rifiutare ordini per motivi legittimi (es. disponibilità prodotto, verifica pagamento). La conferma d'ordine viene inviata via email.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">4. Spedizione</h2>
        <p>La spedizione è gratuita per ordini superiori a €150. Per ordini inferiori, il costo di spedizione è indicato al checkout. I tempi di consegna variano in base alla disponibilità del prodotto e alla destinazione.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">5. Resi e Rimborsi</h2>
        <p>Il consumatore ha diritto di recedere dall'acquisto entro 14 giorni dalla ricezione della merce, ai sensi del D.Lgs. 206/2005. I prodotti devono essere restituiti nelle condizioni originali. Le spese di restituzione sono a carico del cliente.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">6. Garanzia</h2>
        <p>I prodotti sono coperti da garanzia legale di conformità di 2 anni (D.Lgs. 206/2005). Per difetti di conformità, il cliente può richiedere la riparazione, sostituzione, riduzione del prezzo o risoluzione del contratto.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">7. Responsabilità</h2>
        <p>SweetHome non è responsabile per danni derivanti dall'uso improprio dei prodotti acquistati. Le immagini e i contenuti del sito sono protetti da copyright.</p>

        <h2 class="text-xl font-serif text-brand-light mt-8 mb-4">8. Legge Applicabile</h2>
        <p>I presenti termini sono regolati dalla legge italiana. Per qualsiasi controversia, il foro competente è quello di Milano.</p>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Add legal routes**

Modify `routes/web.php`:
```php
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');
Route::view('/terms', 'legal.terms')->name('legal.terms');
```

- [ ] **Step 5: Update footer with legal links**

Modify `resources/views/layouts/app-shop.blade.php` — in the footer, update the legal links section:
```blade
<div class="flex space-x-6 mt-4 md:mt-0">
    <a href="{{ route('legal.privacy') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Privacy Policy</a>
    <a href="{{ route('legal.cookies') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Cookie Policy</a>
    <a href="{{ route('legal.terms') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Termini e Condizioni</a>
</div>
```

- [ ] **Step 6: Commit**

```bash
git add resources/views/legal/ routes/web.php resources/views/layouts/app-shop.blade.php
git commit -m "feat: add legal pages (privacy, cookie, terms) with footer links"
```

---

### Task 5: Performance & Caching

**Files:**
- Modify: `app/Http/Livewire/ShopPage.php`
- Modify: `app/Http/Livewire/ProductDetail.php`
- Modify: `bootstrap/app.php` (or AppServiceProvider)

- [ ] **Step 1: Cache categories and brands in ShopPage**

Modify `app/Http/Livewire/ShopPage.php` — in the `render()` method, wrap the category/brand queries with caching:

```php
use Illuminate\Support\Facades\Cache;

// Replace the categories and brands queries with:
$categories = Cache::remember('shop.categories', 3600, function () {
    return Category::whereNull('parent_id')->where('active', true)->orderBy('sort_order')->get();
});

$brands = Cache::remember('shop.brands', 3600, function () {
    return Brand::where('active', true)->orderBy('sort_order')->get();
});
```

- [ ] **Step 2: Cache related products in ProductDetail**

Modify `app/Http/Livewire/ProductDetail.php` — cache the related products query:

```php
$relatedProducts = Cache::remember(
    "product.related.{$product->id}",
    1800,
    function () use ($product) {
        return Product::published()
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('category_id', $product->categories->pluck('id'));
            })
            ->limit(4)
            ->get();
    }
);
```

- [ ] **Step 3: Add cache clearing on model changes**

Modify `app/Models/Category.php` — add a boot method:
```php
protected static function boot()
{
    parent::boot();
    static::saved(fn() => Cache::forget('shop.categories'));
    static::deleted(fn() => Cache::forget('shop.categories'));
}
```

Modify `app/Models/Brand.php`:
```php
protected static function boot()
{
    parent::boot();
    static::saved(fn() => Cache::forget('shop.brands'));
    static::deleted(fn() => Cache::forget('shop.brands'));
}
```

Modify `app/Models/Product.php`:
```php
protected static function boot()
{
    parent::boot();
    static::saved(function ($product) {
        Cache::forget("product.related.{$product->id}");
        // Also clear related products for products in same categories
        foreach ($product->categories as $cat) {
            $cat->products->each(fn($p) => Cache::forget("product.related.{$p->id}"));
        }
    });
}
```

- [ ] **Step 4: Add route caching in production**

Add to `bootstrap/app.php` or deployment script:
```bash
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/ShopPage.php app/Http/Livewire/ProductDetail.php app/Models/Category.php app/Models/Brand.php app/Models/Product.php
git commit -m "feat: add caching for categories, brands, and related products with automatic cache invalidation"
```

---

### Task 6: Cart Count in Navigation

**Files:**
- Create: `app/Http/Livewire/CartCount.php`
- Create: `resources/views/livewire/cart-count.blade.php`
- Modify: `resources/views/layouts/app-shop.blade.php`

- [ ] **Step 1: Create CartCount Livewire component**

Create `app/Http/Livewire/CartCount.php`:
```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CartCount extends Component
{
    protected $listeners = ['cart-updated' => '$refresh'];

    public function render()
    {
        $count = collect(session()->get('cart', []))->sum('quantity');

        return view('livewire.cart-count', [
            'count' => $count,
        ]);
    }
}
```

- [ ] **Step 2: Create cart count view**

Create `resources/views/livewire/cart-count.blade.php`:
```blade
<span class="absolute -top-2 -right-2 bg-brand-gold text-brand-dark text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
    {{ $count }}
</span>
```

- [ ] **Step 3: Replace static cart badge in nav**

Modify `resources/views/layouts/app-shop.blade.php` — replace the static cart badge with:
```blade
<a href="{{ route('cart') }}" class="relative group">
    <svg class="w-6 h-6 hover:text-brand-gold transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
    </svg>
    @livewire('cart-count')
</a>
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/CartCount.php resources/views/livewire/cart-count.blade.php resources/views/layouts/app-shop.blade.php
git commit -m "feat: add live cart count badge to navigation via Livewire"
```

---

### Task 7: Pre-Launch Checklist & Final Config

**Files:**
- Modify: `.env`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Configure production defaults**

Modify `.env` — ensure these are set for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sweethome.it

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sweethome
DB_USERNAME=...
DB_PASSWORD=...

CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

- [ ] **Step 2: Set default URL in AppServiceProvider**

Modify `app/Providers/AppServiceProvider.php` — in `boot()`:
```php
use Illuminate\Support\Facades\URL;

if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

- [ ] **Step 3: Generate sitemap for production**

Run:
```bash
php artisan sitemap:generate
```

- [ ] **Step 4: Run production optimizations**

Run:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

- [ ] **Step 5: Set up queue worker**

For production, add to supervisor config or deployment script:
```bash
php artisan queue:work --tries=3 --timeout=60
```

- [ ] **Step 6: Commit**

```bash
git add .env.example app/Providers/AppServiceProvider.php
git commit -m "chore: configure production defaults and pre-launch optimizations"
```

---

## Phase 5 Summary

After completing this phase you will have:
- SEO meta tags component with Open Graph and Twitter Card support on every page
- Dynamic sitemap.xml generated from products and categories
- robots.txt blocking admin, cart, checkout, and webhook routes
- Custom 404 and 500 error pages matching the dark luxury theme
- Legal pages: Privacy Policy (GDPR-compliant), Cookie Policy, Terms & Conditions
- Footer links to all legal pages
- Caching for categories, brands, and related products with automatic invalidation on model changes
- Live cart count badge in navigation
- Production-ready configuration (HTTPS forcing, debug off, route/config/view caching)
- Queue worker configured for background email processing
