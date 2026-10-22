<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPolicyTextDotSpacing extends Command
{
    protected $signature = 'product:fix-policy-dot-spacing';

    protected $description = 'Remove extra whitespace before the dot in product policy_text (</span>  .</p> -> </span>.</p>). Skips products that are already clean.';

    public function handle(): int
    {
        $total = DB::table('products')->count();
        $affected = DB::table('products')
            ->whereRaw('policy_text REGEXP \'[[:space:]]+\\.</p>\'')
            ->count();

        $leftover = DB::table('products')
            ->whereRaw('REGEXP_REPLACE(policy_text, \'[[:space:]]+\\.</p>\', \'.</p>\') REGEXP \'[[:space:]]+\\.</p>\'')
            ->count();

        $this->info("Total products: {$total}");
        $this->info("Products with extra space before .</p>: {$affected}");
        $this->line("---");

        if ($affected === 0) {
            $this->info('Nothing to fix. Already clean.');

            return self::SUCCESS;
        }

        DB::table('products')
            ->whereRaw('policy_text REGEXP \'[[:space:]]+\\.</p>\'')
            ->update([
                'policy_text' => DB::raw('REGEXP_REPLACE(policy_text, \'[[:space:]]+\\.</p>\', \'.</p>\')'),
            ]);

        $stillAffected = DB::table('products')
            ->whereRaw('policy_text REGEXP \'[[:space:]]+\\.</p>\'')
            ->count();

        $this->info('Fixed: '.($affected - $stillAffected).' product(s).');
        $this->info('Remaining after fix: '.$stillAffected);
        $this->info('Leftover if re-run: '.$leftover);

        if ($stillAffected > 0) {
            $this->error('Some products still have extra space before .</p>.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}