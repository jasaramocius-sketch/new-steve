<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Tests\TestCase;

class ProductImportSkuTest extends TestCase
{
    public function test_import_resolves_part_number_and_sku_separately(): void
    {
        $identifiers = ProductController::resolveImportedIdentifiers([
            'part_number' => '  bp-1001  ',
            'sku' => '  sku-9001  ',
        ], 'Brake Pads Set');

        $this->assertSame('bp-1001', $identifiers['part_number']);
        $this->assertSame('sku-9001', $identifiers['sku']);
        $this->assertNotSame($identifiers['part_number'], $identifiers['sku']);
    }

    public function test_import_does_not_use_part_number_as_sku(): void
    {
        $product = new Product(['part_number' => 'existing-pn', 'sku' => 'existing-sku']);
        $product->id = 99;

        $sku = ProductController::resolveImportedSku(['part_number' => 'bp-1001'], 'Brake Pads Set', $product);

        $this->assertSame('existing-sku', $sku);
    }

    public function test_import_does_not_use_sku_as_part_number(): void
    {
        $product = new Product(['part_number' => 'existing-pn', 'sku' => 'existing-sku']);
        $product->id = 99;

        $partNumber = ProductController::resolveImportedPartNumber(['sku' => 'sku-9001'], 'Brake Pads Set', $product);

        $this->assertSame('existing-pn', $partNumber);
    }

    public function test_import_provided_values_take_precedence_over_existing_values(): void
    {
        $product = new Product(['part_number' => 'existing-pn', 'sku' => 'existing-sku']);
        $product->id = 99;

        $identifiers = ProductController::resolveImportedIdentifiers([
            'part_number' => 'new-pn',
            'sku' => 'new-sku',
        ], 'Brake Pads Set', $product);

        $this->assertSame('new-pn', $identifiers['part_number']);
        $this->assertSame('new-sku', $identifiers['sku']);
    }

    public function test_import_preserves_existing_values_when_csv_cells_are_empty(): void
    {
        $product = new Product(['part_number' => 'existing-pn', 'sku' => 'existing-sku']);
        $product->id = 99;

        $identifiers = ProductController::resolveImportedIdentifiers([
            'part_number' => '',
            'sku' => '',
        ], 'Brake Pads Set', $product);

        $this->assertSame('existing-pn', $identifiers['part_number']);
        $this->assertSame('existing-sku', $identifiers['sku']);
    }
}
