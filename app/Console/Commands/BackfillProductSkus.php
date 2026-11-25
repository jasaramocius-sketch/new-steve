<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillProductSkus extends Command
{
    protected $signature = 'products:backfill-skus {--dry-run : Preview without saving} {--chunk=5000 : Products per update batch}';

    protected $description = 'Generate a unique SKU for every product that has no SKU';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(100, min(20000, (int) $this->option('chunk')));

        $missingQuery = DB::table('products')->where(function ($query) {
            $query->whereNull('sku')->orWhere('sku', '');
        });

        $missing = (clone $missingQuery)->count();

        if ($missing === 0) {
            $this->info('Every product already has a SKU. Nothing to do.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $sample = (clone $missingQuery)->orderBy('id')->limit(5)->get(['id', 'name']);
            $this->warn('[DRY-RUN] No products were changed.');
            $this->info("{$missing} product(s) would receive a SKU.");
            foreach ($sample as $product) {
                $this->line("[{$product->id}] {$product->name} -> SKU-{$product->id}");
            }

            return self::SUCCESS;
        }

        $updated = 0;

        while (true) {
            // A deterministic, id-based SKU is unique without a per-row existence query.
            $ids = (clone $missingQuery)->orderBy('id')->limit($chunk)->pluck('id');
            if ($ids->isEmpty()) {
                break;
            }

            $affected = DB::table('products')
                ->whereIn('id', $ids)
                ->where(function ($query) {
                    $query->whereNull('sku')->orWhere('sku', '');
                })
                ->update(['sku' => DB::raw("CONCAT('SKU-', id)")]);

            $updated += $affected;
            $this->line("Updated {$updated} of {$missing} product(s)...");

            if ($affected === 0) {
                break;
            }
        }

        $this->info("Done. {$updated} product(s) received a SKU.");

        return self::SUCCESS;
    }
}
