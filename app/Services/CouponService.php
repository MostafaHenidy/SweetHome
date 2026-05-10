<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CouponService
{
    public function findValidCoupon(string $code, ?User $user, array $cart): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon) {
            throw new \InvalidArgumentException('Coupon non trovato.');
        }

        $this->assertCouponIsValid($coupon, $user, $cart);

        return $coupon;
    }

    public function validateCoupon(string $code, ?User $user, array $cart): bool
    {
        $this->findValidCoupon($code, $user, $cart);

        return true;
    }

    public function assertCouponIsValid(Coupon $coupon, ?User $user, array $cart): void
    {
        if (! $user) {
            throw new \InvalidArgumentException('Accedi al tuo account per utilizzare un coupon.');
        }

        if (! $coupon->is_active) {
            throw new \InvalidArgumentException('Coupon non attivo.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw new \InvalidArgumentException('Coupon non ancora valido.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new \InvalidArgumentException('Coupon scaduto.');
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            throw new \InvalidArgumentException('Coupon esaurito.');
        }

        if ($user && $coupon->usages()->where('user_id', $user->id)->exists()) {
            throw new \InvalidArgumentException('Hai gia utilizzato questo coupon.');
        }

        if ($coupon->type === 'product_specific' && ! $this->cartContainsProduct($cart, (int) $coupon->product_id)) {
            throw new \InvalidArgumentException('Questo coupon e valido solo per un prodotto specifico.');
        }
    }

    public function calculateDiscount(Coupon $coupon, Order|array $target): float
    {
        $subtotal = $target instanceof Order
            ? (float) $target->subtotal
            : $this->cartSubtotal($target);

        $productSubtotal = $coupon->product_id
            ? ($target instanceof Order ? $this->orderProductSubtotal($target, (int) $coupon->product_id) : $this->cartProductSubtotal($target, (int) $coupon->product_id))
            : $subtotal;

        // Debug: Log the calculation inputs
        \Log::info('CouponService calculateDiscount inputs:', [
            'coupon_type' => $coupon->type,
            'coupon_value' => $coupon->value,
            'coupon_product_id' => $coupon->product_id,
            'subtotal' => $subtotal,
            'productSubtotal' => $productSubtotal,
            'target_type' => is_array($target) ? 'array' : 'order',
        ]);

        $discount = match ($coupon->type) {
            'fixed_money' => (float) $coupon->value,
            'fixed_points' => app(PointsService::class)->convertPointsToEuros((int) $coupon->value),
            'percentage' => $subtotal * ((float) $coupon->value / 100),
            'product_specific' => $productSubtotal * ((float) $coupon->value / 100),
            default => 0,
        };

        $finalDiscount = round(min(max($discount, 0), $subtotal), 2);

        // Debug: Log the calculation result
        \Log::info('CouponService calculateDiscount result:', [
            'discount_before_rounding' => $discount,
            'final_discount' => $finalDiscount,
            'subtotal' => $subtotal,
        ]);

        return $finalDiscount;
    }

    public function applyCoupon(Coupon $coupon, Order $order): float
    {
        $discount = $this->calculateDiscount($coupon, $order);

        if ($discount <= 0 || ! $order->user_id) {
            return $discount;
        }

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'discount_amount' => $discount,
        ]);

        $coupon->increment('used_count');

        return $discount;
    }

    public function bulkGenerate(array $config): Collection
    {
        $count = max(1, min((int) ($config['count'] ?? 1), 500));
        $prefix = strtoupper((string) ($config['prefix'] ?? 'SH'));

        return collect(range(1, $count))->map(function () use ($config, $prefix): Coupon {
            return Coupon::create([
                'code' => $this->uniqueCode($prefix),
                'type' => $config['type'],
                'value' => $config['value'],
                'product_id' => $config['product_id'] ?? null,
                'max_uses' => $config['max_uses'] ?? null,
                'starts_at' => $config['starts_at'] ?? null,
                'expires_at' => $config['expires_at'] ?? null,
                'is_active' => $config['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);
        });
    }

    protected function uniqueCode(string $prefix): string
    {
        do {
            $code = $prefix.'-'.strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    protected function cartSubtotal(array $cart): float
    {
        return collect($cart)->sum(fn (array $item): float => (float) $item['price'] * (int) $item['quantity']);
    }

    protected function cartProductSubtotal(array $cart, int $productId): float
    {
        return collect($cart)
            ->filter(fn (array $item): bool => (int) ($item['product_id'] ?? 0) === $productId)
            ->sum(fn (array $item): float => (float) $item['price'] * (int) $item['quantity']);
    }

    protected function orderProductSubtotal(Order $order, int $productId): float
    {
        return $order->items()
            ->where('product_id', $productId)
            ->get()
            ->sum(fn ($item): float => (float) $item->total);
    }

    protected function cartContainsProduct(array $cart, int $productId): bool
    {
        return collect($cart)->contains(fn (array $item): bool => (int) ($item['product_id'] ?? 0) === $productId);
    }
}
