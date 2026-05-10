<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request, CartService $cartService)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartService->addToCart(
                $request->product_id,
                $request->quantity ?? 1,
                $request->attributes ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Prodotto aggiunto al carrello!',
                'cart_count' => $cartService->getCount(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiunta al carrello',
            ], 500);
        }
    }
}
