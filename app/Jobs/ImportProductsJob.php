<?php

namespace App\Jobs;

use App\Http\Controllers\ProductController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Seller;
use App\Support\BulkOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 1;

    private const PROGRESS_EVERY = 100;

    private const MAX_STORED_ERRORS = 50;

    public function __construct(
        private readonly string $operationId,
        private readonly string $path,
        private readonly string $originalName,
    ) {}

    public function handle(): void
    {
        if (! BulkOperation::get($this->operationId)) {
            return;
        }

        BulkOperation::update($this->operationId, [
            'status' => 'processing',
            'message' => 'Reading CSV file...',
        ]);

        $absolutePath = Storage::disk('local')->path($this->path);
        $handle = @fopen($absolutePath, 'r');
        if (! is_resource($handle)) {
            $this->markFailed('The uploaded CSV could not be opened.');

            return;
        }

        try {
            $header = fgetcsv($handle, 0, ',');
            if (! is_array($header)) {
                $this->markFailed('The uploaded CSV appears to be empty.');

                return;
            }

            $expected = ['id', 'name', 'part_number', 'sku', 'price', 'old_price', 'category', 'stock', 'description', 'badge', 'product_type', 'status', 'featured', 'image', 'brand', 'seller', 'year', 'make', 'model', 'gallery_images', 'policy_text', 'buy_return_policy', 'return_policy', 'features', 'feature_list', 'reviews', 'reviews_data', 'specifications', 'tab_label_1', 'tab_label_2', 'tab_label_3'];

            $colMap = [];
            foreach ($header as $i => $column) {
                $column = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', trim((string) $column)));
                if (in_array($column, $expected, true)) {
                    $colMap[$i] = $column;
                }
            }

            if (! in_array('name', $colMap, true) || ! in_array('price', $colMap, true)) {
                $this->markFailed('CSV must contain at least "name" and "price" columns.');

                return;
            }

            $total = 0;
            while (fgetcsv($handle, 0, ',') !== false) {
                $total++;
            }
            rewind($handle);
            fgetcsv($handle, 0, ','); // Skip the header row again.

            BulkOperation::update($this->operationId, [
                'total' => $total,
                'message' => "Importing 0 of {$total} row(s)...",
            ]);

            $categories = Category::all()->keyBy(fn ($category) => strtolower(trim($category->name)));
            $brands = Brand::all()->keyBy(fn ($brand) => strtolower(trim($brand->name)));
            $sellers = Seller::all()->keyBy(fn ($seller) => strtolower(trim($seller->name)));

            $processed = 0;
            $imported = 0;
            $skipped = 0;
            $categoriesCreated = 0;
            $brandsCreated = 0;
            $sellersCreated = 0;
            $galleryImported = 0;
            $errors = [];
            $errorCount = 0;
            $rowNum = 1;

            $persist = function (bool $force = false) use (
                &$processed, &$total, &$imported, &$skipped, &$errors, &$errorCount,
                &$categoriesCreated, &$brandsCreated, &$sellersCreated, &$galleryImported,
            ): void {
                if (! $force && $processed % self::PROGRESS_EVERY !== 0) {
                    return;
                }

                BulkOperation::update($this->operationId, [
                    'processed' => $processed,
                    'total' => $total,
                    'message' => "Importing {$processed} of {$total} row(s)...",
                    'errors' => array_slice($errors, -self::MAX_STORED_ERRORS),
                    'error_count' => $errorCount,
                    'result' => [
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'categories_created' => $categoriesCreated,
                        'brands_created' => $brandsCreated,
                        'sellers_created' => $sellersCreated,
                        'gallery_images_imported' => $galleryImported,
                    ],
                ]);
            };

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $processed++;
                $rowNum++;
                $row = array_map('trim', $row);
                $data = [];

                foreach ($colMap as $colIdx => $colName) {
                    $data[$colName] = $row[$colIdx] ?? '';
                }

                if (empty($data['name'])) {
                    $errorCount++;
                    $errors[] = "Row {$rowNum}: name is required.";
                    $skipped++;
                    $persist();

                    continue;
                }

                if (! is_numeric($data['price']) || $data['price'] < 0) {
                    $errorCount++;
                    $errors[] = "Row {$rowNum}: price must be a positive number.";
                    $skipped++;
                    $persist();

                    continue;
                }

                try {
                    $existingProduct = null;
                    if (! empty($data['id']) && is_numeric($data['id'])) {
                        $existingProduct = Product::find((int) $data['id']);
                    }

                    $categoryId = null;
                    if (! empty($data['category'])) {
                        $catKey = strtolower(trim($data['category']));
                        if (isset($categories[$catKey])) {
                            $categoryId = $categories[$catKey]->id;
                        } else {
                            $newCat = Category::create([
                                'name' => trim($data['category']),
                                'slug' => Str::slug(trim($data['category'])),
                                'status' => true,
                            ]);
                            $categories[$catKey] = $newCat;
                            $categoryId = $newCat->id;
                            $categoriesCreated++;
                        }
                    }

                    $brandId = null;
                    if (! empty($data['brand'])) {
                        $brandKey = strtolower(trim($data['brand']));
                        if (isset($brands[$brandKey])) {
                            $brandId = $brands[$brandKey]->id;
                        } else {
                            $newBrand = Brand::create([
                                'name' => trim($data['brand']),
                                'slug' => Str::slug(trim($data['brand'])),
                                'status' => true,
                            ]);
                            $brands[$brandKey] = $newBrand;
                            $brandId = $newBrand->id;
                            $brandsCreated++;
                        }
                    }

                    $sellerId = null;
                    if (! empty($data['seller'])) {
                        $sellerKey = strtolower(trim($data['seller']));
                        if (isset($sellers[$sellerKey])) {
                            $sellerId = $sellers[$sellerKey]->id;
                        } else {
                            $newSeller = Seller::create([
                                'name' => trim($data['seller']),
                                'slug' => Str::slug(trim($data['seller'])).'-'.uniqid(),
                                'status' => true,
                            ]);
                            $sellers[$sellerKey] = $newSeller;
                            $sellerId = $newSeller->id;
                            $sellersCreated++;
                        }
                    }

                    $normalized = ProductController::normalizeImportedProductData($data);

                    $insertData = [
                        'name' => $data['name'],
                        'price' => $data['price'],
                        'old_price' => (! empty($data['old_price']) && is_numeric($data['old_price'])) ? $data['old_price'] : null,
                        'category_id' => $categoryId,
                        'brand_id' => $brandId,
                        'seller_id' => $sellerId,
                        'year' => (! empty($data['year']) && is_numeric($data['year'])) ? (int) $data['year'] : null,
                        'make' => $data['make'] ?? null,
                        'model' => $data['model'] ?? null,
                        'stock' => (! empty($data['stock']) && is_numeric($data['stock'])) ? (int) $data['stock'] : 0,
                        'description' => $normalized['description'] ?: ($data['description'] ?? ''),
                        'badge' => $data['badge'] ?? null,
                        'product_type' => in_array($data['product_type'] ?? '', ['none', 'new_arrival', 'trending', 'best_selling', 'popular']) ? $data['product_type'] : 'none',
                        'status' => in_array(($data['status'] ?? ''), ['1', 'yes', 'active', 'true'], true),
                        'featured' => in_array(($data['featured'] ?? ''), ['1', 'yes', 'active', 'true'], true),
                        'policy_text' => $normalized['policy_text'],
                        'features' => $normalized['features'],
                        'reviews_data' => $normalized['reviews_data'],
                        'specifications' => ProductController::parseSpecifications($data['specifications'] ?? null),
                        'tab_label_1' => $data['tab_label_1'] ?? null,
                        'tab_label_2' => $data['tab_label_2'] ?? null,
                        'tab_label_3' => $data['tab_label_3'] ?? null,
                    ];

                    if ($existingProduct) {
                        $insertData['slug'] = $existingProduct->slug;
                        $insertData['part_number'] = ProductController::resolveImportedSku($data, $data['name'], $existingProduct);
                    } else {
                        $insertData['added_by'] = 'admin';
                        $insertData['part_number'] = ProductController::resolveImportedSku($data, $data['name']);
                        $insertData['slug'] = Str::slug($data['name']).'-'.time().'-'.$imported;
                    }

                    if (! empty($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
                        $saved = saveImageFromUrlWithWebp($data['image']);
                        if ($saved) {
                            $insertData['image'] = $saved;
                        } else {
                            $errorCount++;
                            $errors[] = "Row {$rowNum}: image URL could not be downloaded or was not a valid image.";
                        }
                    }

                    if ($existingProduct) {
                        $existingProduct->update($insertData);
                        $product = $existingProduct;
                    } else {
                        $product = Product::create($insertData);
                    }
                    $imported++;

                    if (! empty($data['gallery_images'])) {
                        $galleryUrls = array_filter(array_map('trim', explode('|', $data['gallery_images'])));
                        $subdir = 'uploads/'.now()->format('Y/m');
                        $destDir = storage_path('app/public/'.$subdir);
                        if (! is_dir($destDir)) {
                            mkdir($destDir, 0775, true);
                        }

                        foreach ($galleryUrls as $imgUrl) {
                            if (! filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                                continue;
                            }

                            $response = Http::timeout(30)->get($imgUrl);
                            if ($response->failed()) {
                                continue;
                            }

                            $contentType = (string) $response->header('Content-Type');
                            if (! str_contains($contentType, 'image/')) {
                                continue;
                            }

                            $ext = 'jpg';
                            if (str_contains($contentType, 'png')) {
                                $ext = 'png';
                            } elseif (str_contains($contentType, 'webp')) {
                                $ext = 'webp';
                            } elseif (str_contains($contentType, 'gif')) {
                                $ext = 'gif';
                            }

                            $filename = time().'_'.uniqid().'.'.$ext;
                            file_put_contents($destDir.'/'.$filename, $response->body());
                            $info = @getimagesize($destDir.'/'.$filename);

                            Image::create([
                                'original_name' => $filename,
                                'filename' => $filename,
                                'path' => $subdir.'/'.$filename,
                                'url' => 'storage/'.$subdir.'/'.$filename,
                                'mime_type' => $contentType,
                                'size' => filesize($destDir.'/'.$filename),
                                'width' => $info[0] ?? null,
                                'height' => $info[1] ?? null,
                                'is_unused' => false,
                                'attachable_type' => Product::class,
                                'attachable_id' => $product->id,
                            ]);
                            $galleryImported++;
                        }
                    }
                } catch (Throwable $e) {
                    $errorCount++;
                    $errors[] = "Row {$rowNum}: ".$e->getMessage();
                    $skipped++;
                }

                $persist();
            }

            $extras = [];
            if ($categoriesCreated > 0) {
                $extras[] = "{$categoriesCreated} new category(ies) created";
            }
            if ($brandsCreated > 0) {
                $extras[] = "{$brandsCreated} new brand(s) created";
            }
            if ($sellersCreated > 0) {
                $extras[] = "{$sellersCreated} new seller(s) created";
            }
            if ($galleryImported > 0) {
                $extras[] = "{$galleryImported} gallery image(s) imported";
            }

            $message = "Imported {$imported} product(s) successfully from {$processed} row(s).";
            if ($extras) {
                $message .= ' ('.implode(', ', $extras).')';
            }
            if ($errorCount > 0) {
                $message .= ' '.$errorCount.' error(s).';
            }

            BulkOperation::update($this->operationId, [
                'status' => 'completed',
                'processed' => $processed,
                'total' => $total,
                'message' => $message,
                'errors' => array_slice($errors, -self::MAX_STORED_ERRORS),
                'error_count' => $errorCount,
                'result' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'categories_created' => $categoriesCreated,
                    'brands_created' => $brandsCreated,
                    'sellers_created' => $sellersCreated,
                    'gallery_images_imported' => $galleryImported,
                ],
            ]);

            Storage::disk('local')->delete($this->path);
        } catch (Throwable $e) {
            $this->markFailed($e->getMessage());
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
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
