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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingCategory() { $this->resetPage(); }
    public function updatingBrand() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'brand', 'sort', 'minPrice', 'maxPrice']);
    }

    public function render()
    {
        $query = Product::query()->published();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('short_description', 'like', '%' . $this->search . '%');
            });
        }

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

        if ($this->brand) {
            $query->where('brand_id', $this->brand);
        }

        if ($this->minPrice) { $query->where('price', '>=', $this->minPrice); }
        if ($this->maxPrice) { $query->where('price', '<=', $this->maxPrice); }

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
