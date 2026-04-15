<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class Cart
{
    public static function add($id, $name, $price, $image, $quantity = 1)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity,
            ];
        }

        Session::put('cart', $cart);
    }

    public static function remove($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
    }

    public static function update($id, $quantity)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            if ($quantity <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = $quantity;
            }
            Session::put('cart', $cart);
        }
    }

    public static function items()
    {
        return Session::get('cart', []);
    }

    public static function count()
    {
        $cart = Session::get('cart', []);
        return array_sum(array_column($cart, 'quantity'));
    }

    public static function subtotal()
    {
        $cart = Session::get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public static function clear()
    {
        Session::forget('cart');
    }
}
