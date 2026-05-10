<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BackfillQuoteItemsSeeder extends Seeder
{
    public function run(): void
    {
        $quotes = Order::where('type', 'quote_request')
            ->doesntHave('items')
            ->get();

        if ($quotes->isEmpty()) {
            $this->command->info('No quote orders without items found.');

            return;
        }

        $products = Product::whereNotNull('price')
            ->where('price', '>', 0)
            ->take(20)
            ->get();

        if ($products->isEmpty()) {
            $this->command->error('No products available to backfill.');

            return;
        }

        foreach ($quotes as $order) {
            $remaining = (float) $order->total;
            $itemsAdded = 0;

            foreach ($products->shuffle() as $product) {
                if ($remaining <= 0) {
                    break;
                }

                $price = (float) $product->price;
                if ($price <= 0 || $price > $remaining) {
                    continue;
                }

                $qty = min(3, (int) floor($remaining / $price));
                if ($qty < 1) {
                    continue;
                }

                $lineTotal = $price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? '',
                    'line_description' => $product->quoteLineDescription(),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $lineTotal,
                    'vat_rate' => $product->vat_rate,
                ]);

                $remaining -= $lineTotal;
                $itemsAdded++;

                if ($itemsAdded >= 3) {
                    break;
                }
            }

            if ($remaining > 0 && $itemsAdded > 0) {
                $lastItem = $order->items()->latest('id')->first();
                if ($lastItem) {
                    $lastItem->update([
                        'total' => (float) $lastItem->total + $remaining,
                    ]);
                }
            }

            $this->command->info("  {$order->order_number}: added {$itemsAdded} items");
        }
    }
}
