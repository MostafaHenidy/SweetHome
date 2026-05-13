<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get cart items for current user/session
     */
    public function getCartItems()
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())
                ->with('product')
                ->get()
                ->map(function (CartItem $item) {
                    if (! $item->product) {
                        return null;
                    }

                    return $this->buildCartItem(
                        (string) $item->id,
                        (int) $item->product_id,
                        (int) $item->quantity,
                        $item->attribute_selection ?? [],
                        $item->product
                    );
                })
                ->filter()
                ->values();
        }

        // For guests, use session
        $sessionCart = Session::get('cart', []);
        $cartItems = [];

        foreach ($sessionCart as $key => $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $cartItems[] = $this->buildCartItem(
                    (string) $key,
                    (int) $item['product_id'],
                    (int) $item['quantity'],
                    $item['attributes'] ?? [],
                    $product
                );
            }
        }

        return collect($cartItems);
    }

    /**
     * Add item to cart
     */
    public function addToCart($productId, $quantity = 1, $attributes = [])
    {
        $product = Product::findOrFail($productId);

        if (Auth::check()) {
            // Database cart for logged-in users
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->where('attribute_selection', json_encode($attributes))
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
            } else {
                CartItem::create([
                    'session_id' => Session::getId(),
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'attribute_selection' => $attributes,
                ]);
            }
        } else {
            // Session cart for guests
            $cart = Session::get('cart', []);
            $key = $productId.'-'.md5(json_encode($attributes));

            if (isset($cart[$key])) {
                $cart[$key]['quantity'] += $quantity;
            } else {
                $cart[$key] = [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => ImageHelper::getProductMainImage($product),
                    'quantity' => $quantity,
                    'attributes' => $attributes,
                ];
            }

            Session::put('cart', $cart);
        }

        return true;
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity($itemId, $quantity)
    {
        if (Auth::check()) {
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('id', $itemId)
                ->firstOrFail();

            if ($quantity <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->update(['quantity' => $quantity]);
            }
        } else {
            $cart = Session::get('cart', []);
            if (isset($cart[$itemId])) {
                if ($quantity <= 0) {
                    unset($cart[$itemId]);
                } else {
                    $cart[$itemId]['quantity'] = $quantity;
                }
                Session::put('cart', $cart);
            }
        }

        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem($itemId)
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())
                ->where('id', $itemId)
                ->delete();
        } else {
            $cart = Session::get('cart', []);
            unset($cart[$itemId]);
            Session::put('cart', $cart);
        }

        return true;
    }

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->delete();
        } else {
            Session::forget('cart');
        }

        return true;
    }

    /**
     * Get cart total
     */
    public function getTotal()
    {
        return $this->getCartItems()->sum('total_price');
    }

    /**
     * Get cart count
     */
    public function getCount()
    {
        return $this->getCartItems()->sum('quantity');
    }

    /**
     * Convert cart to order items format
     */
    public function prepareOrderItems()
    {
        $cartItems = $this->getCartItems();
        $orderItems = [];

        foreach ($cartItems as $item) {
            $product = $item->product;
            $defaultVat = (float) Setting::get('default_vat_rate', 22);
            $orderItems[] = [
                'product_id' => $item->product_id,
                'product_name' => $product->name,
                'product_sku' => $product->sku ?? null,
                'line_description' => $product->quoteLineDescription(),
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'attribute_selection' => $item->attribute_selection ?? [],
                'vat_rate' => $product->vat_rate !== null ? (float) $product->vat_rate : $defaultVat,
            ];
        }

        return $orderItems;
    }

    private function buildCartItem(string $id, int $productId, int $quantity, array $attributes, Product $product): object
    {
        $unitPrice = (float) $product->price;
        $defaultVat = (float) Setting::get('default_vat_rate', 22);

        return (object) [
            'id' => $id,
            'product_id' => $productId,
            'product' => $product,
            'name' => $product->name,
            'line_description' => $product->quoteLineDescription(),
            'price' => $unitPrice,
            'image' => ImageHelper::getProductMainImage($product),
            'quantity' => $quantity,
            'attributes' => $attributes,
            'attribute_selection' => $attributes,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'vat_rate' => $product->vat_rate !== null ? (float) $product->vat_rate : $defaultVat,
        ];
    }

}
