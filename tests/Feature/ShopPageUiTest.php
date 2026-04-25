<?php

namespace Tests\Feature;

use App\Http\Livewire\ShopPage;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopPageUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_renders_the_showroom_shell(): void
    {
        Livewire::test(ShopPage::class)
            ->assertSee('SweetHome Milano')
            ->assertSee('IL LUSSO')
            ->assertSee('DI ABITARE')
            ->assertSee('Cerca')
            ->assertSee('Categorie')
            ->assertSee('Marchi')
            ->assertSee('Prezzo');
    }

    public function test_product_card_renders_the_gold_cta_and_price_unit(): void
    {
        $product = new Product([
            'sku' => 'SKU-001',
            'name' => 'Marmo Calacatta Oro',
            'slug' => 'marmo-calacatta-oro',
            'price' => 250,
            'status' => 'published',
        ]);
        $product->setRelation('brand', new Brand(['name' => 'StoneLab']));

        $html = view('components.product-card', ['product' => $product])->render();

        $this->assertStringContainsString('Aggiungi al carrello', $html);
        $this->assertStringContainsString('€250,00 / mq', $html);
    }

    public function test_shop_route_returns_ok(): void
    {
        $this->get(route('shop'))->assertOk();
    }
}
