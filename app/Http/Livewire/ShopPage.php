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

    protected $listeners = ['cart-updated' => '$refresh'];

    public function render()
    {
        $query = Product::query()->published()->with(['activeCoupons', 'variants.media', 'coupons' => function($q) {
            $q->where('type', 'product_specific')->active();
        }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('short_description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->category) {
            $category = Category::where('slug', $this->category)->first();
            if ($category) {
                $categoryIds = $this->categoryAndDescendantIds($category);
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        if ($this->brand) {
            $query->where('brand_id', $this->brand);
        }

        if ($this->minPrice) {
            $query->where('price', '>=', $this->minPrice);
        }
        if ($this->maxPrice) {
            $query->where('price', '<=', $this->maxPrice);
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(27);
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

    protected function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];

        $category->loadMissing('children.children');

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->categoryAndDescendantIds($child));
        }

        return array_values(array_unique($ids));
    }

    // Helper method to check if product has active discounts
    public function hasActiveDiscount($product)
    {
        return $product->coupons->where('type', 'product_specific')
            ->where('is_active', true)
            ->filter(function($coupon) {
                $now = now();
                return ($coupon->starts_at === null || $coupon->starts_at <= $now) &&
                       ($coupon->expires_at === null || $coupon->expires_at > $now) &&
                       ($coupon->max_uses === null || $coupon->used_count < $coupon->max_uses);
            })->count() > 0;
    }
}
