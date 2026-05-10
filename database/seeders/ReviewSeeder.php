<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()
            ->published()
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No published products found. Seed products before seeding reviews.');

            return;
        }

        $reviews = [
            [
                'name' => 'Giulia Conti',
                'email' => 'giulia.conti@example.com',
                'rating' => 5,
                'comment' => 'Servizio impeccabile e materiali davvero curati. Il parquet ha cambiato completamente l atmosfera della nostra casa.',
            ],
            [
                'name' => 'Marco Rinaldi',
                'email' => 'marco.rinaldi@example.com',
                'rating' => 5,
                'comment' => 'Consegna puntuale, assistenza gentile e prodotto esattamente come descritto. Si vede la qualita italiana.',
            ],
            [
                'name' => 'Elena Ferri',
                'email' => 'elena.ferri@example.com',
                'rating' => 4,
                'comment' => 'Ottima esperienza dall ordine al montaggio. Le finiture sono eleganti e facili da abbinare agli arredi.',
            ],
            [
                'name' => 'Luca Moretti',
                'email' => 'luca.moretti@example.com',
                'rating' => 5,
                'comment' => 'Avevamo bisogno di un consiglio tecnico e il team ci ha seguito con molta attenzione. Risultato finale bellissimo.',
            ],
            [
                'name' => 'Sara Bianchi',
                'email' => 'sara.bianchi@example.com',
                'rating' => 5,
                'comment' => 'Prodotto elegante, robusto e molto piu bello dal vivo. Lo consiglierei senza dubbi.',
            ],
            [
                'name' => 'Andrea Greco',
                'email' => 'andrea.greco@example.com',
                'rating' => 4,
                'comment' => 'Buon rapporto qualita prezzo e comunicazione chiara. La casa ora ha un aspetto molto piu raffinato.',
            ],
        ];

        foreach ($reviews as $index => $review) {
            $product = $products[$index % $products->count()];

            Review::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_email' => $review['email'],
                ],
                [
                    'user_name' => $review['name'],
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'status' => 'approved',
                    'show_on_home' => true,
                    'created_at' => now()->subDays($index + 1),
                    'updated_at' => now()->subDays($index + 1),
                ]
            );
        }
    }
}
