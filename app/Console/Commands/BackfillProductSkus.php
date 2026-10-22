<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class BackfillProductSkus extends Command
{
    protected $signature = 'products:backfill-skus {--dry-run : Preview without saving}';

    protected $description = 'Generate an auto SKU for every product that has none';

    public function handle()
    {
        $products = Product::whereNull('sku')->orWhere('sku', '')->get();
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        foreach ($products as $product) {
            $sku = Product::generateSku((string) $product->name, (int) $product->id);
            $this->line("[{$product->id}] {$product->name} -> {$sku}");

            if (! $dryRun) {
                $product->forceFill(['sku' => $sku])->save();
            }
            $updated++;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '')."Done. {$updated} products affected.");
    }
}