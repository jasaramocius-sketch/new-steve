<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartStockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('vendor_stock')->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('main_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $productId = $this->createProduct(0);

        $this->postJson(route('cart.add'), [
            'product_id' => $productId,
            'product_name' => 'Forged product name',
            'product_price' => 0.01,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This product is out of stock.');

        $this->assertSessionMissing('cart');
    }

    public function test_cart_quantity_cannot_exceed_available_stock(): void
    {
        $productId = $this->createProduct(2);

        $this->postJson(route('cart.add'), ['product_id' => $productId, 'qty' => 2])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson(route('cart.add'), ['product_id' => $productId, 'qty' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(2, session('cart')[$productId]['qty']);
    }

    public function test_cart_uses_product_details_from_database(): void
    {
        $productId = $this->createProduct(5);

        $this->postJson(route('cart.add'), [
            'product_id' => $productId,
            'product_name' => 'Forged product name',
            'product_price' => 0.01,
        ])->assertOk();

        $cartItem = session('cart')[$productId];
        $this->assertSame('Product '.$productId, $cartItem['name']);
        $this->assertSame('12.50', $cartItem['price']);
    }

    private function createProduct(int $stock): int
    {
        return DB::table('products')->insertGetId([
            'name' => 'Product',
            'status' => true,
            'vendor_stock' => $stock,
            'cost' => 12.50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}