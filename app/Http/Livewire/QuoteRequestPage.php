<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\QuoteService;
use Livewire\Component;

class QuoteRequestPage extends Component
{
    public $step = 1;

    // Customer information
    public $customer_name = '';

    public $customer_email = '';

    public $customer_phone = '';

    public $company_name = '';

    public $address = '';

    public $city = '';

    public $state = '';

    public $zip_code = '';

    public $country = 'IT';

    // Additional notes
    public $notes = '';

    // Cart items
    public $cartItems = [];

    protected $rules = [
        'customer_name' => 'required|string|min:2|max:255',
        'customer_email' => 'required|email|max:255',
        'customer_phone' => 'nullable|string|max:20',
        'company_name' => 'nullable|string|max:255',
        'address' => 'required|string|max:500',
        'city' => 'required|string|max:100',
        'state' => 'required|string|max:100',
        'zip_code' => 'required|string|max:20',
        'country' => 'required|string|max:2',
    ];

    public function mount(CartService $cartService)
    {
        // Debug: Check session cart directly
        $sessionCart = session()->get('cart', []);
        \Log::info('QuoteRequestPage mount - session cart', [
            'session_cart' => $sessionCart,
            'session_cart_count' => count($sessionCart),
        ]);

        // Get cart items
        $cartItemsCollection = $cartService->getCartItems();
        $this->cartItems = $cartItemsCollection->toArray();

        \Log::info('QuoteRequestPage mount - cart service', [
            'cart_items_count' => $cartItemsCollection->count(),
            'cart_items' => $this->cartItems,
        ]);

        if (empty($this->cartItems)) {
            \Log::warning('QuoteRequestPage: Cart is empty, redirecting to cart page');

            return redirect()->route('cart');
        }

        // Pre-fill user data if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $this->customer_name = $user->name;
            $this->customer_email = $user->email;
            $this->customer_phone = $user->phone ?? '';
            $this->address = $user->address ?? '';
            $this->city = $user->city ?? '';
            $this->state = $user->state ?? '';
            $this->zip_code = $user->zip_code ?? '';
            $this->country = $user->country ?? 'IT';
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'customer_name' => 'required|string|min:2|max:255',
                'customer_email' => 'required|email|max:255',
            ]);
        }

        if ($this->step === 2) {
            $this->validate([
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'zip_code' => 'required|string|max:20',
                'country' => 'required|string|max:2',
            ]);
        }

        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function submitQuoteRequest(CartService $cartService, QuoteService $quoteService)
    {
        $this->validate();

        // Prepare customer data
        $customerData = [
            'name' => $this->customer_name,
            'email' => $this->customer_email,
            'phone' => $this->customer_phone,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'country' => $this->country,
            'notes' => $this->notes,
        ];

        // Use component's already-loaded cart items (re-fetching via prepareOrderItems
        // can return empty if the session/cart changed between mount and submit).
        $orderItems = collect($this->cartItems)->map(function ($item) {
            $product = Product::find(data_get($item, 'product_id'));

            return [
                'product_id' => data_get($item, 'product_id'),
                'product_name' => data_get($item, 'name') ?? ($product?->name ?? 'Prodotto'),
                'product_sku' => $product?->sku ?? '',
                'line_description' => $product?->quoteLineDescription() ?? (data_get($item, 'name') ?? 'Prodotto'),
                'quantity' => (int) (data_get($item, 'quantity') ?? 1),
                'unit_price' => (float) (data_get($item, 'unit_price') ?? data_get($item, 'price') ?? 0),
                'total_price' => (float) (data_get($item, 'total_price') ?? 0),
                'attribute_selection' => data_get($item, 'attribute_selection') ?? data_get($item, 'attributes') ?? [],
                'vat_rate' => $product?->vat_rate !== null
                    ? (float) $product->vat_rate
                    : (float) Setting::get('default_vat_rate', 22),
            ];
        })->toArray();

        if (empty($orderItems)) {
            $this->addError('submit', 'Il carrello è vuoto. Aggiungi prodotti prima di richiedere un preventivo.');
            $this->step = 1;

            return;
        }

        try {
            $order = $quoteService->createQuoteRequest($orderItems, $customerData);

            \Log::info('Quote request created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'items_count' => $order->items->count(),
            ]);

            $quoteService->generatePdf($order);

            // Clear cart
            $cartService->clearCart();

            // Redirect to success page with the order number
            return redirect()->route('quote.success', ['orderNumber' => $order->order_number]);

        } catch (\Exception $e) {
            \Log::error('Error creating quote request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('submit', 'Error creating quote request: '.$e->getMessage());
            $this->step = 1;
        }
    }

    public function getSubtotalProperty()
    {
        return collect($this->cartItems)->sum('total_price');
    }

    public function getShippingCostProperty()
    {
        return 0; // Quote requests don't include shipping initially
    }

    public function getTaxProperty()
    {
        return 0; // Tax will be calculated when quote is accepted
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->shippingCost + $this->tax;
    }

    public function render()
    {
        return view('livewire.quote-request-page');
    }
}
