# Phase 1: Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Set up the database schema, core models, relationships, FilamentPHP admin panel, and base application structure.

**Architecture:** Laravel 12 + FilamentPHP 3 admin panel + Spatie MediaLibrary. Models follow the spec schema. Filament handles all admin CRUD. Jetstream auth is already installed.

**Tech Stack:** Laravel 12, FilamentPHP 3, Spatie Laravel MediaLibrary, Laravel Sanctum, MySQL

**Prerequisites:** Homepage already built (`resources/views/home.blade.php`, `resources/views/layouts/app-home.blade.php`, Tailwind config updated). Jetstream + Livewire already installed.

---

### Task 1: Install FilamentPHP and Spatie MediaLibrary

**Files:**
- Modify: `composer.json` (new dependencies)

- [ ] **Step 1: Install FilamentPHP**

Run:
```bash
composer require filament/filament:"^3.2" -W
php artisan filament:install --panels
```

- [ ] **Step 2: Install Spatie MediaLibrary**

Run:
```bash
composer require spatie/laravel-medialibrary:"^11.0"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
```

- [ ] **Step 3: Run migrations**

Run:
```bash
php artisan migrate
```

Expected: All Filament, MediaLibrary, and existing migrations run successfully.

- [ ] **Step 4: Create admin user**

Run:
```bash
php artisan make:filament-user
```
Enter: name=admin, email=admin@sweethome.it, password=admin123

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock database/migrations/*
git commit -m "feat: install FilamentPHP admin panel and Spatie MediaLibrary"
```

---

### Task 2: Create Database Migrations

**Files:**
- Create: `database/migrations/2026_04_15_200001_create_brands_table.php`
- Create: `database/migrations/2026_04_15_200002_create_categories_table.php`
- Create: `database/migrations/2026_04_15_200003_create_products_table.php`
- Create: `database/migrations/2026_04_15_200004_create_product_category_table.php`
- Create: `database/migrations/2026_04_15_200005_create_product_attributes_table.php`
- Create: `database/migrations/2026_04_15_200006_create_cart_items_table.php`
- Create: `database/migrations/2026_04_15_200007_create_orders_table.php`
- Create: `database/migrations/2026_04_15_200008_create_order_items_table.php`
- Create: `database/migrations/2026_04_15_200009_create_reviews_table.php`
- Create: `database/migrations/2026_04_15_200010_create_contact_submissions_table.php`
- Create: `database/migrations/2026_04_15_200011_create_settings_table.php`
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php` (add phone column)

- [ ] **Step 1: Add phone column to users table**

Create migration:
```bash
php artisan make:migration add_phone_to_users_table --table=users
```

Content for `database/migrations/2026_04_15_200000_add_phone_to_users_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
```

- [ ] **Step 2: Create brands migration**

```bash
php artisan make:migration create_brands_table
```

Content for `database/migrations/2026_04_15_200001_create_brands_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
```

- [ ] **Step 3: Create categories migration**

```bash
php artisan make:migration create_categories_table
```

Content for `database/migrations/2026_04_15_200002_create_categories_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('level')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

- [ ] **Step 4: Create products migration**

```bash
php artisan make:migration create_products_table
```

Content for `database/migrations/2026_04_15_200003_create_products_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_stock')->default(false);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

- [ ] **Step 5: Create product_category pivot migration**

```bash
php artisan make:migration create_product_category_table
```

Content for `database/migrations/2026_04_15_200004_create_product_category_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
```

- [ ] **Step 6: Create product_attributes migration**

```bash
php artisan make:migration create_product_attributes_table
```

Content for `database/migrations/2026_04_15_200005_create_product_attributes_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('values');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
```

- [ ] **Step 7: Create cart_items migration**

```bash
php artisan make:migration create_cart_items_table
```

Content for `database/migrations/2026_04_15_200006_create_cart_items_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('attribute_selection')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
```

- [ ] **Step 8: Create orders migration**

```bash
php artisan make:migration create_orders_table
```

Content for `database/migrations/2026_04_15_200007_create_orders_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

- [ ] **Step 9: Create order_items migration**

```bash
php artisan make:migration create_order_items_table
```

Content for `database/migrations/2026_04_15_200008_create_order_items_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->json('attribute_selection')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
```

- [ ] **Step 10: Create reviews migration**

```bash
php artisan make:migration create_reviews_table
```

Content for `database/migrations/2026_04_15_200009_create_reviews_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('user_name');
            $table->string('user_email');
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
```

- [ ] **Step 11: Create contact_submissions migration**

```bash
php artisan make:migration create_contact_submissions_table
```

Content for `database/migrations/2026_04_15_200010_create_contact_submissions_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
```

- [ ] **Step 12: Create settings migration**

```bash
php artisan make:migration create_settings_table
```

Content for `database/migrations/2026_04_15_200011_create_settings_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

- [ ] **Step 13: Run all migrations**

Run:
```bash
php artisan migrate
```

Expected: All tables created successfully.

- [ ] **Step 14: Commit**

```bash
git add database/migrations/
git commit -m "feat: create all database migrations for e-commerce schema"
```

---

### Task 3: Create Eloquent Models

**Files:**
- Create: `app/Models/Brand.php`
- Create: `app/Models/Category.php`
- Create: `app/Models/Product.php`
- Create: `app/Models/ProductAttribute.php`
- Create: `app/Models/CartItem.php`
- Create: `app/Models/Order.php`
- Create: `app/Models/OrderItem.php`
- Create: `app/Models/Review.php`
- Create: `app/Models/ContactSubmission.php`
- Create: `app/Models/Setting.php`
- Modify: `app/Models/User.php` (add phone fillable, cart/orders relationships)

- [ ] **Step 1: Create Brand model**

Create `app/Models/Brand.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name', 'slug', 'description', 'sort_order', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->withResponsiveImages();
    }
}
```

- [ ] **Step 2: Create Category model**

Create `app/Models/Category.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'level', 'sort_order', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($ancestors, $parent);
            $parent = $parent->parent;
        }
        return $ancestors;
    }

    public function breadcrumb(): array
    {
        return [...$this->ancestors(), $this];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->withResponsiveImages();
    }
}
```

- [ ] **Step 3: Create Product model**

Create `app/Models/Product.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'sku', 'name', 'slug', 'short_description', 'description',
        'price', 'compare_price', 'cost_price', 'brand_id',
        'stock_quantity', 'track_stock', 'status', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getFormattedPriceAttribute(): string
    {
        return '€' . number_format($this->price, 2, ',', '.');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->withResponsiveImages();

        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->crop('crop-center');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->crop('crop-center');
    }
}
```

- [ ] **Step 4: Create ProductAttribute model**

Create `app/Models/ProductAttribute.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

- [ ] **Step 5: Create CartItem model**

Create `app/Models/CartItem.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'user_id', 'product_id', 'attribute_selection', 'quantity',
    ];

    protected $casts = [
        'attribute_selection' => 'array',
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }
}
```

- [ ] **Step 6: Create Order model**

Create `app/Models/Order.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email',
        'customer_phone', 'shipping_address', 'billing_address',
        'subtotal', 'shipping_cost', 'tax', 'total',
        'status', 'payment_method', 'payment_status', 'notes',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '€' . number_format($this->total, 2, ',', '.');
    }
}
```

- [ ] **Step 7: Create OrderItem model**

Create `app/Models/OrderItem.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_sku',
        'attribute_selection', 'quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'attribute_selection' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

- [ ] **Step 8: Create Review model**

Create `app/Models/Review.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_name', 'user_email', 'rating', 'comment', 'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
```

- [ ] **Step 9: Create ContactSubmission model**

Create `app/Models/ContactSubmission.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status',
    ];
}
```

- [ ] **Step 10: Create Setting model**

Create `app/Models/Setting.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'group',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? ($setting->value ?? $default) : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
```

- [ ] **Step 11: Update User model**

Modify `app/Models/User.php` — add `phone` to fillable and add relationships:

```php
// Add to $fillable array:
'phone',

// Add these methods:
public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(CartItem::class);
}

public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Order::class);
}
```

- [ ] **Step 12: Commit**

```bash
git add app/Models/
git commit -m "feat: create all Eloquent models with relationships"
```

---

### Task 4: Create Filament Resources

**Files:**
- Create: `app/Filament/Resources/BrandResource.php`
- Create: `app/Filament/Resources/BrandResource/Pages/ListBrands.php`
- Create: `app/Filament/Resources/BrandResource/Pages/CreateBrand.php`
- Create: `app/Filament/Resources/BrandResource/Pages/EditBrand.php`
- Create: `app/Filament/Resources/CategoryResource.php`
- Create: `app/Filament/Resources/CategoryResource/Pages/ListCategories.php`
- Create: `app/Filament/Resources/CategoryResource/Pages/CreateCategory.php`
- Create: `app/Filament/Resources/CategoryResource/Pages/EditCategory.php`
- Create: `app/Filament/Resources/ProductResource.php`
- Create: `app/Filament/Resources/ProductResource/Pages/ListProducts.php`
- Create: `app/Filament/Resources/ProductResource/Pages/CreateProduct.php`
- Create: `app/Filament/Resources/ProductResource/Pages/EditProduct.php`
- Create: `app/Filament/Resources/OrderResource.php`
- Create: `app/Filament/Resources/OrderResource/Pages/ListOrders.php`
- Create: `app/Filament/Resources/OrderResource/Pages/ViewOrder.php`
- Create: `app/Filament/Resources/ReviewResource.php`
- Create: `app/Filament/Resources/ReviewResource/Pages/ListReviews.php`
- Create: `app/Filament/Resources/ReviewResource/Pages/EditReview.php`
- Create: `app/Filament/Resources/ContactSubmissionResource.php`
- Create: `app/Filament/Resources/ContactSubmissionResource/Pages/ListContactSubmissions.php`
- Create: `app/Filament/Resources/ContactSubmissionResource/Pages/ViewContactSubmission.php`
- Create: `app/Filament/Pages/Settings.php`
- Create: `app/Filament/Widgets/StatsOverview.php`
- Create: `app/Filament/Widgets/RecentOrders.php`

- [ ] **Step 1: Generate Filament resources via artisan**

Run these commands to scaffold the resources:
```bash
php artisan make:filament-resource Brand --generate
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Product --generate
php artisan make:filament-resource Order --generate --view
php artisan make:filament-resource Review --generate
php artisan make:filament-resource ContactSubmission --generate --view
```

- [ ] **Step 2: Customize BrandResource**

Modify `app/Filament/Resources/BrandResource.php` — ensure the form has:
- TextInput for name (with slug generator)
- Textarea for description
- FileUpload for logo (media collection)
- Toggle for active
- TextInput for sort_order

And the table has:
- TextColumn for name (searchable, sortable)
- IconColumn for active
- TextColumn for sort_order
- Actions: Edit, Delete

- [ ] **Step 3: Customize CategoryResource**

Modify `app/Filament/Resources/CategoryResource.php` — ensure the form has:
- Select for parent_id (nullable, options from all categories)
- TextInput for name (with slug generator)
- Textarea for description
- Select for level (1-4)
- TextInput for sort_order
- Toggle for active
- FileUpload for image (media collection)

Table: tree view or flat with parent column, searchable, sortable.

- [ ] **Step 4: Customize ProductResource**

Modify `app/Filament/Resources/ProductResource.php` — form with tabs:
- **Tab "Generale":** SKU, name (slug), brand select, short description, description (rich editor), status toggle
- **Tab "Prezzo":** price, compare_price, cost_price
- **Tab "Inventario":** stock_quantity, track_stock toggle
- **Tab "Categorie":** checkbox list or select for categories (many-to-many)
- **Tab "Immagini":** file upload (multiple) for images media collection
- **Tab "Attributi":** repeater for product attributes (name + JSON values)

Table: searchable by name/SKU, filterable by brand/category/status, sortable.

- [ ] **Step 5: Customize OrderResource**

Modify `app/Filament/Resources/OrderResource.php`:
- List table: order_number, customer_name, total, status badge, payment_status badge, created_at
- View page: show order details, items table (repeater read-only), addresses, status update action
- Actions: update status, mark as paid

- [ ] **Step 6: Customize ReviewResource**

Modify `app/Filament/Resources/ReviewResource.php`:
- Table: product name, user_name, rating (star display), status badge, created_at
- Filters: by status, by product
- Actions: approve, reject, delete

- [ ] **Step 7: Customize ContactSubmissionResource**

Modify `app/Filament/Resources/ContactSubmissionResource.php`:
- Table: name, email, subject, status badge, created_at
- View page: show full message, mark as read/replied actions

- [ ] **Step 8: Create Settings page**

Create `app/Filament/Pages/Settings.php`:
```php
<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name', 'SweetHome Milano'),
            'site_email' => Setting::get('site_email', 'info@sweethome.it'),
            'site_phone' => Setting::get('site_phone', '328 899 5628'),
            'site_address' => Setting::get('site_address', 'Via Carlo Espinasse 104/a, 20156 Milano MI'),
            'vat_number' => Setting::get('vat_number', ''),
            'tax_code' => Setting::get('tax_code', ''),
            'shipping_cost' => Setting::get('shipping_cost', 0),
            'free_shipping_threshold' => Setting::get('free_shipping_threshold', 0),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informazioni Sito')
                    ->schema([
                        TextInput::make('site_name')->label('Nome Sito'),
                        TextInput::make('site_email')->label('Email'),
                        TextInput::make('site_phone')->label('Telefono'),
                        Textarea::make('site_address')->label('Indirizzo'),
                        TextInput::make('vat_number')->label('Partita IVA'),
                        TextInput::make('tax_code')->label('Codice Fiscale'),
                    ])->columns(2),
                Section::make('Spedizione')
                    ->schema([
                        TextInput::make('shipping_cost')->label('Costo Spedizione (€)')->numeric(),
                        TextInput::make('free_shipping_threshold')->label('Soglia Spedizione Gratuita (€)')->numeric(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
        $this->notify('success', 'Impostazioni salvate con successo.');
    }
}
```

Create `resources/views/filament/pages/settings.blade.php`:
```blade
<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>
```

- [ ] **Step 9: Create StatsOverview widget**

Create `app/Filament/Widgets/StatsOverview.php`:
```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Prodotti', Product::count())
                ->description('Totale prodotti nel catalogo')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Ordini', Order::count())
                ->description('Totale ordini')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Recensioni in attesa', Review::where('status', 'pending')->count())
                ->description('Da approvare')
                ->icon('heroicon-o-star'),
            Stat::make('Entrate', '€' . number_format(Order::where('payment_status', 'paid')->sum('total'), 2, ',', '.'))
                ->description('Totale incassato')
                ->icon('heroicon-o-euro'),
        ];
    }
}
```

- [ ] **Step 10: Create RecentOrders widget**

Create `app/Filament/Widgets/RecentOrders.php`:
```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('EUR', locale: 'it_IT'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i'),
            ]);
    }
}
```

- [ ] **Step 11: Register widgets in Filament panel provider**

Modify `app/Providers/Filament/AdminPanelProvider.php` — add widgets to dashboard:
```php
->widgets([
    Widgets\StatsOverview::class,
    Widgets\RecentOrders::class,
])
```

- [ ] **Step 12: Commit**

```bash
git add app/Filament/ resources/views/filament/
git commit -m "feat: create Filament admin resources and widgets"
```

---

### Task 5: Create Database Seeders

**Files:**
- Create: `database/seeders/BrandSeeder.php`
- Create: `database/seeders/CategorySeeder.php`
- Create: `database/seeders/ProductSeeder.php`
- Create: `database/seeders/SettingSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create BrandSeeder**

Create `database/seeders/BrandSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'LA FABBRICA AVA',
            'PANARIA CERAMICHE',
            'MGM CERAMICHE',
            'ITALWOOD DESIGN',
            'EXPOSERVICE',
            'OFFICINE ITALIANE PARQUET',
            'CERAMICHE DOLOMITE',
            'VITRUVIT',
            'COLOMBO DESIGN',
        ];

        foreach ($brands as $name) {
            Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'active' => true,
            ]);
        }
    }
}
```

- [ ] **Step 2: Create CategorySeeder**

Create `database/seeders/CategorySeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pavimenti' => [
                'Gres',
                'Parquet',
                'Pavimenti per esterni',
            ],
            'Battiscopa' => [
                'PVC',
                'MDF',
                'Legno',
                'Laminati',
                'Gres',
            ],
            'Rivestimenti' => [],
            'Mosaici' => [],
            'Arredo Bagno' => [
                'Mobili',
                'Accessori',
                'Complementi',
                'Specchi',
            ],
            'Sanitari' => [
                'Vasche',
                'Complementi',
                'Bidet / WC / Lavabi',
            ],
            'Docce' => [
                'Piatti doccia',
                'Cabine doccia',
                'Box doccia',
            ],
            'Rubinetteria' => [
                'Cucina',
                'Bagno',
            ],
            'Riscaldamento' => [
                'Termoarredi',
                'Radiatori',
                'Complementi',
            ],
            'Porte' => [],
            'Finestre' => [],
            'Maniglie' => [
                'Contemporanee',
                'Maniglie a incasso',
                'Maniglioni',
                'Pomoli',
            ],
        ];

        foreach ($categories as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'level' => 1,
                'active' => true,
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'level' => 2,
                    'active' => true,
                ]);
            }
        }
    }
}
```

- [ ] **Step 3: Create ProductSeeder**

Create `database/seeders/ProductSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::all();
        $categories = Category::all();

        $sampleProducts = [
            [
                'name' => 'Abisko Grigio 20x120',
                'sku' => 'ABISKO-GR-20120',
                'price' => 45.90,
                'category_slugs' => ['gres', 'pavimenti'],
                'brand_slug' => 'la-fabbrica-ava',
                'short_description' => 'Gres porcellanato effetto pietra, formato 20x120 cm.',
                'attributes' => [
                    ['name' => 'Dimensioni', 'values' => ['20x120x0,9', '20x180x1,5', '30x180x1,5']],
                ],
            ],
            [
                'name' => 'Rovere Naturale Parquet',
                'sku' => 'ROVERE-NAT-14',
                'price' => 89.00,
                'category_slugs' => ['parquet', 'pavimenti'],
                'brand_slug' => 'italwood-design',
                'short_description' => 'Parquet in rovere naturale, spessore 14mm.',
                'attributes' => [
                    ['name' => 'Spessore', 'values' => ['14mm', '20mm']],
                ],
            ],
            [
                'name' => 'Lavabo Sospeso Bianco',
                'sku' => 'LAVABO-SOSP-BI',
                'price' => 120.00,
                'category_slugs' => ['bidet-wc-lavabi', 'sanitari'],
                'brand_slug' => 'ceramiche-dolomite',
                'short_description' => 'Lavabo sospeso in ceramica bianca, design moderno.',
                'attributes' => [],
            ],
            [
                'name' => 'Maniglia Colombo Design C33',
                'sku' => 'COL-C33-CR',
                'price' => 35.50,
                'category_slugs' => ['contemporanee', 'maniglie'],
                'brand_slug' => 'colombo-design',
                'short_description' => 'Maniglia contemporanea Colombo Design modello C33.',
                'attributes' => [
                    ['name' => 'Finitura', 'values' => ['Cromo', 'Nichel Satinato', 'Nero Opaco']],
                ],
            ],
        ];

        foreach ($sampleProducts as $data) {
            $brand = $brands->firstWhere('slug', $data['brand_slug']);
            $categoryIds = Category::whereIn('slug', $data['category_slugs'])->pluck('id');

            $product = Product::create([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'price' => $data['price'],
                'brand_id' => $brand?->id,
                'short_description' => $data['short_description'],
                'description' => '<p>' . $data['short_description'] . '</p><p>Prodotto di alta qualità, made in Italy.</p>',
                'status' => 'published',
                'stock_quantity' => rand(10, 100),
            ]);

            $product->categories()->attach($categoryIds);

            foreach ($data['attributes'] as $attr) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'name' => $attr['name'],
                    'values' => $attr['values'],
                ]);
            }
        }
    }
}
```

- [ ] **Step 4: Create SettingSeeder**

Create `database/seeders/SettingSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'SweetHome Milano', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@sweethome.it', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '328 899 5628', 'group' => 'general'],
            ['key' => 'site_address', 'value' => 'Via Carlo Espinasse 104/a, 20156 Milano MI', 'group' => 'general'],
            ['key' => 'vat_number', 'value' => '', 'group' => 'general'],
            ['key' => 'tax_code', 'value' => '', 'group' => 'general'],
            ['key' => 'shipping_cost', 'value' => 9.90, 'group' => 'shipping'],
            ['key' => 'free_shipping_threshold', 'value' => 150.00, 'group' => 'shipping'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
```

- [ ] **Step 5: Update DatabaseSeeder**

Modify `database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
```

- [ ] **Step 6: Run seeders**

Run:
```bash
php artisan db:seed
```

Expected: Brands, categories, products, and settings seeded successfully.

- [ ] **Step 7: Commit**

```bash
git add database/seeders/
git commit -m "feat: add database seeders for brands, categories, products, settings"
```

---

### Task 6: Configure MediaLibrary Storage

**Files:**
- Modify: `config/filesystems.php` (ensure public disk configured)
- Modify: `config/media-library.php` (configure disk)

- [ ] **Step 1: Ensure public disk is configured**

Check `config/filesystems.php` has:
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
],
```

- [ ] **Step 2: Create storage symlink**

Run:
```bash
php artisan storage:link
```

- [ ] **Step 3: Configure MediaLibrary to use public disk**

In `config/media-library.php`, set:
```php
'disk_name' => 'public',
```

- [ ] **Step 4: Commit**

```bash
git add config/
git commit -m "feat: configure MediaLibrary storage"
```

---

### Task 7: Verify Admin Panel Works

**Files:** No new files.

- [ ] **Step 1: Start dev server**

Run:
```bash
php artisan serve
```

- [ ] **Step 2: Access admin panel**

Open browser to `http://127.0.0.1:8000/admin`
Login with admin@sweethome.it / admin123

Verify:
- Dashboard shows stats widgets
- Can navigate to Brands, Categories, Products, Orders, Reviews, Contact Submissions
- Can create/edit/delete a brand
- Can create/edit/delete a category
- Can create/edit/delete a product
- Settings page loads and saves

- [ ] **Step 3: Stop server**

```bash
# Ctrl+C
```

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "chore: verify admin panel functionality"
```

---

## Phase 1 Summary

After completing this phase you will have:
- All database tables created and seeded with sample data
- All Eloquent models with proper relationships
- Full FilamentPHP admin panel with CRUD for all entities
- Dashboard with stats overview and recent orders widget
- Settings page for site configuration
- MediaLibrary configured for product/category images
