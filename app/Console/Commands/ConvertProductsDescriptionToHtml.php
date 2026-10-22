<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertProductsDescriptionToHtml extends Command
{
    protected $signature = 'products:convert-description-html {--dry-run : Report only, make no changes}';

    protected $description = 'Convert plain-text/markdown product descriptions to HTML using the same marked (breaks+gfm) renderer used by the frontend';

    private const MARKED_SCRIPT = __DIR__.'/ConvertDescriptionsToHtml.js';

    public function handle(): int
    {
        $rows = DB::table('products')
            ->whereNotNull('description')
            ->where('description', '<>', '')
            ->select('id', 'description')
            ->get();

        $markdown = [];
        foreach ($rows as $row) {
            if (preg_match('/<[a-zA-Z][^>]*>/', (string) $row->description)) {
                continue;
            }
            $markdown[] = ['id' => $row->id, 'text' => $row->description];
        }

        if (count($markdown) === 0) {
            $this->info('No plain-text descriptions to convert.');

            return self::SUCCESS;
        }

        $this->info('Found '.$rows->count().' descriptions in total, '.count($markdown).' plain-text/markdown to convert.');

        $payload = json_encode($markdown);
        $script = tempnam(sys_get_temp_dir(), 'markd_');
        file_put_contents($script, $payload);

        $esc = escapeshellarg($script);
        $cmd = 'node '.escapeshellarg(self::MARKED_SCRIPT)." < {$esc} 2>&1";
        exec($cmd, $output, $exitCode);
        @unlink($script);

        if ($exitCode !== 0) {
            $this->error('node conversion failed: '.implode("\n", $output));

            return self::FAILURE;
        }

        $converted = json_decode(implode("\n", $output), true);
        if (! is_array($converted)) {
            $this->error('Unexpected node output.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info(sprintf('%d description(s) would be converted to HTML.', count($converted)));
            foreach (array_slice($converted, 0, 3) as $item) {
                $this->line('--- id='.$item['id'].' ---');
                $this->line(trim($item['html']));
            }

            return self::SUCCESS;
        }

        $affected = 0;
        DB::transaction(function () use ($converted, &$affected) {
            foreach ($converted as $item) {
                DB::table('products')->where('id', $item['id'])->update(['description' => $item['html']]);
                $affected++;
            }
        });

        $this->info("Converted {$affected} descriptions to HTML.");

        return self::SUCCESS;
    }
}
