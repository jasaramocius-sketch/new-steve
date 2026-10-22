<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Tests\TestCase;

class ProductImportSkuTest extends TestCase
{
    public function test_import_uses_provided_sku_when_present(): void
    {
        $sku = ProductController::resolveImportedSku(['sku' => '  bp-1001  '], 'Brake Pads Set');

        $this->assertSame('bp-1001', $sku);
    }

    public function test_import_provided_sku_takes_precedence_over_existing_sku(): void
    {
        $product = new Product(['sku' => 'existing-123']);
        $product->id = 99;

        $sku = ProductController::resolveImportedSku(['sku' => 'new-456'], 'Brake Pads Set', $product);

        $this->assertSame('new-456', $sku);
    }

    public function test_import_preserves_existing_sku_when_csv_cell_empty(): void
    {
        $product = new Product(['sku' => 'existing-123']);
        $product->id = 99;

        $sku = ProductController::resolveImportedSku(['sku' => ''], 'Brake Pads Set', $product);

        $this->assertSame('existing-123', $sku);
    }
}
