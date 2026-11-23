<?php

namespace App\Jobs;

use App\Models\Product;
use App\Support\BulkOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 1;

    private const CHUNK_SIZE = 500;

    private const HEADERS = [
        'id', 'name', 'part_number', 'price', 'old_price', 'category', 'stock', 'description',
        'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year',
        'make', 'model', 'gallery_images', 'policy_text', 'features', 'specifications',
        'reviews_data', 'tab_label_1', 'tab_label_2', 'tab_label_3',
    ];

    public function __construct(
        private readonly string $operationId,
        private readonly string $fileName,
    ) {}

    public function handle(): void
    {
        if (! BulkOperation::get($this->operationId)) {
            return;
        }

        BulkOperation::update($this->operationId, [
            'status' => 'processing',
            'message' => 'Preparing product export...',
        ]);

        $path = 'bulk-exports/'.$this->operationId.'.csv';

        try {
            $total = Product::count();
            Storage::disk('local')->makeDirectory('bulk-exports');

            $handle = @fopen(Storage::disk('local')->path($path), 'w');
            if (! is_resource($handle)) {
                $this->markFailed('The export file could not be created. Check storage permissions.');

                return;
            }

            try {
                fputcsv($handle, self::HEADERS);

                BulkOperation::update($this->operationId, [
                    'total' => $total,
                    'message' => "Exporting 0 of {$total} product(s)...",
                ]);

                $processed = 0;

                Product::with('category', 'brand', 'seller', 'galleryImages')
                    ->chunkById(self::CHUNK_SIZE, function ($products) use ($handle, $total, &$processed): void {
                        foreach ($products as $product) {
                            $galleryUrls = $product->galleryImages->map(function ($image) {
                                return $image->url ? url($image->url) : '';
                            })->filter()->values()->implode('|');

                            $features = is_array($product->features)
                                ? implode("\n", array_filter($product->features))
                                : '';

                            $specLines = [];
                            foreach ((array) $product->specifications as $spec) {
                                $label = trim($spec['label'] ?? '');
                                $value = trim($spec['value'] ?? '');
                                $specLines[] = ($label !== '' && $value !== '') ? $label.'::'.$value : ($label ?: $value);
                            }
                            $specifications = implode("\n", array_filter($specLines));

                            $reviewsData = is_array($product->reviews_data) && $product->reviews_data
                                ? json_encode($product->reviews_data)
                                : '';

                            fputcsv($handle, [
                                $product->id,
                                $product->name,
                                $product->part_number ?? '',
                                $product->price,
                                $product->old_price ?? '',
                                $product->category->name ?? '',
                                $product->stock ?? 0,
                                $product->description ?? '',
                                $product->badge ?? '',
                                $product->product_type ?? 'none',
                                $product->status ? '1' : '0',
                                $product->featured ? '1' : '0',
                                $product->image ? url(storedPath($product->image, 'assets/images/thumbnails')) : '',
                                $product->brand->name ?? '',
                                $product->seller->name ?? '',
                                $product->year ?? '',
                                $product->make ?? '',
                                $product->model ?? '',
                                $galleryUrls,
                                $product->policy_text ?? '',
                                $features,
                                $specifications,
                                $reviewsData,
                                $product->tab_label_1 ?? '',
                                $product->tab_label_2 ?? '',
                                $product->tab_label_3 ?? '',
                            ]);

                            $processed++;
                        }

                        BulkOperation::update($this->operationId, [
                            'processed' => $processed,
                            'total' => $total,
                            'message' => "Exporting {$processed} of {$total} product(s)...",
                            'result' => ['exported' => $processed],
                        ]);
                    });
            } finally {
                fclose($handle);
            }

            BulkOperation::update($this->operationId, [
                'status' => 'completed',
                'processed' => $processed ?? 0,
                'total' => $total,
                'message' => "Export completed: {$processed} product(s) written.",
                'result' => ['exported' => $processed ?? 0],
                'export_path' => $path,
                'download_url' => route('admin.products.bulk-download', $this->operationId),
                'file_name' => $this->fileName,
            ]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            $this->markFailed($e->getMessage());
        }
    }

    private function markFailed(string $message): void
    {
        BulkOperation::update($this->operationId, [
            'status' => 'failed',
            'message' => $message,
            'errors' => array_merge(BulkOperation::get($this->operationId)['errors'] ?? [], [$message]),
        ]);
    }
}
