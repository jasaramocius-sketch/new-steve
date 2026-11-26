<?php

namespace App\Services;

use App\Models\Product;
use App\Support\StockSync;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class StockFeedSyncService
{
    private const CHUNK_SIZE = 500;

    /**
     * Fetch the configured supplier feed and update only products present in it.
     *
     * @return array{status:string,message:string,matched:int,feed_rows:int,skipped:int}
     */
    public function run(bool $dryRun = false, bool $force = false): array
    {
        $settings = StockSync::settings();

        if (! $force && ! StockSync::configured($settings)) {
            $message = 'Skipped: supplier feed integration is not configured (add a feed URL and enable sync).';
            StockSync::recordResult($message);

            return [
                'status' => 'skipped',
                'message' => $message,
                'matched' => 0,
                'feed_rows' => 0,
                'skipped' => 0,
            ];
        }

        $url = trim((string) $settings['stock_feed_url']);
        if ($url === '') {
            throw new RuntimeException('Stock feed URL is not configured.');
        }

        try {
            $body = $this->fetch($url, $settings);
            $rows = $this->parse($body, $settings);
            [$map, $skipped] = $this->normalize($rows, $settings);
            $matched = $this->apply($map, $settings, $dryRun);

            $message = sprintf(
                '%s Synced %d of %d feed rows (%d skipped, %d not found).',
                $dryRun ? 'Dry run:' : 'Completed:',
                $matched,
                count($map),
                $skipped,
                max(0, count($map) - $matched)
            );

            StockSync::recordResult($message);

            return [
                'status' => 'synced',
                'message' => $message,
                'matched' => $matched,
                'feed_rows' => count($map),
                'skipped' => $skipped,
            ];
        } catch (Throwable $e) {
            StockSync::recordResult('Failed: '.$e->getMessage());

            throw $e;
        }
    }

    private function fetch(string $url, array $settings): string
    {
        $accept = $settings['stock_feed_format'] === 'json' ? 'application/json' : 'text/csv';
        $request = Http::withHeaders(['Accept' => $accept])->timeout(60);

        $token = trim((string) $settings['stock_feed_token']);
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get($url);
        if (! $response->successful()) {
            throw new RuntimeException('Supplier feed returned HTTP '.$response->status());
        }

        return $response->body();
    }

    private function parse(string $body, array $settings): array
    {
        if ($settings['stock_feed_format'] === 'csv') {
            return $this->parseCsv($body);
        }

        return $this->parseJson($body, (string) $settings['stock_feed_items_path']);
    }

    private function parseJson(string $body, string $itemsPath): array
    {
        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Supplier feed did not return valid JSON.');
        }

        $itemsPath = trim($itemsPath);
        if ($itemsPath !== '') {
            foreach (explode('.', $itemsPath) as $segment) {
                $segment = trim($segment);
                if ($segment === '' || ! is_array($decoded) || ! array_key_exists($segment, $decoded)) {
                    throw new RuntimeException('Supplier feed path "'.$itemsPath.'" was not found.');
                }
                $decoded = $decoded[$segment];
            }
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Supplier feed items must be an array.');
        }

        if (! array_is_list($decoded)) {
            $first = reset($decoded);
            $decoded = is_array($first) ? array_values($decoded) : [$decoded];
        }

        return $decoded;
    }

    private function parseCsv(string $body): array
    {
        $handle = fopen('php://temp', 'r+');
        if (! is_resource($handle)) {
            throw new RuntimeException('Could not open a temporary stream for the CSV feed.');
        }

        fwrite($handle, $body);
        rewind($handle);

        $header = fgetcsv($handle);
        if (! is_array($header) || $header === []) {
            fclose($handle);

            return [];
        }

        $header = array_map(static fn ($column) => trim((string) $column), $header);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $columnCount = count($header);

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $row = array_slice($row, 0, $columnCount);
            $rows[] = array_combine($header, array_pad($row, $columnCount, ''));
        }

        fclose($handle);

        return $rows;
    }

    private function normalize(array $rows, array $settings): array
    {
        $keyField = (string) $settings['stock_feed_key_field'];
        $stockField = (string) $settings['stock_feed_stock_field'];

        $map = [];
        $skipped = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $skipped++;
                continue;
            }

            $key = trim((string) ($row[$keyField] ?? ''));
            $rawStock = $row[$stockField] ?? null;

            if ($key === '' || ! is_numeric($rawStock)) {
                $skipped++;
                continue;
            }

            $map[$key] = max(0, (int) $rawStock);
        }

        return [$map, $skipped];
    }

    private function apply(array $map, array $settings, bool $dryRun): int
    {
        if ($map === []) {
            return 0;
        }

        $column = (string) $settings['stock_feed_key_field'];
        $matched = 0;

        foreach (array_chunk($map, self::CHUNK_SIZE, true) as $chunk) {
            $idsByKey = Product::query()
                ->whereIn($column, array_keys($chunk))
                ->pluck('id', $column);

            if ($idsByKey->isEmpty()) {
                continue;
            }

            $grouped = [];
            foreach ($chunk as $key => $stock) {
                if (! $idsByKey->has($key)) {
                    continue;
                }

                $grouped[(int) $stock][] = (int) $idsByKey->get($key);
            }

            foreach ($grouped as $stock => $ids) {
                if (! $dryRun) {
                    Product::whereIn('id', $ids)->update(['vendor_stock' => $stock]);
                }

                $matched += count($ids);
            }
        }

        return $matched;
    }
}
