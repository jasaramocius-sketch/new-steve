<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SourceS3ImageUrlTest extends TestCase
{
    public function test_it_generates_a_fresh_url_for_source_bucket_images(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);

        $disk = new class
        {
            public ?string $requestedPath = null;

            public function temporaryUrl(string $path, \DateTimeInterface $expiration): string
            {
                $this->requestedPath = $path;

                return 'https://signed.example/image.jpg';
            }
        };

        Storage::shouldReceive('disk')
            ->times(3)
            ->with('source_images')
            ->andReturn($disk);

        $sourceUrl = 'https://autoparts-hub.s3.amazonaws.com/steveautoparts/images/ZW-Gates%20Waterpump/ZW_42207_Ang.jpg?X-Amz-Expires=900';
        $url = storedImageUrl($sourceUrl);
        $detailUrl = storedPath($sourceUrl);
        $s3Url = storedImageUrl('s3://autoparts-hub/steveautoparts/images/ZW-Gates Waterpump/ZW_42207_Ang.jpg');

        $this->assertSame('https://signed.example/image.jpg', $url);
        $this->assertSame($url, $detailUrl);
        $this->assertSame($url, $s3Url);
        $this->assertSame('steveautoparts/images/ZW-Gates Waterpump/ZW_42207_Ang.jpg', $disk->requestedPath);
    }

    public function test_it_leaves_other_external_image_urls_unchanged(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);

        $url = 'https://images.example.com/product.jpg';

        $this->assertSame($url, storedImageUrl($url));
    }

    public function test_product_image_uses_an_aws_gallery_image_instead_of_a_local_path(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);
        $product = new Product;
        $product->setRawAttributes([
            'main_image' => 'uploads/local-product.jpg',
            'images_json' => json_encode([
                ['file' => 's3://autoparts-hub/steveautoparts/images/brand/part_Fro.jpg'],
            ]),
        ]);

        $this->assertSame('s3://autoparts-hub/steveautoparts/images/brand/part_Fro.jpg', $product->image);
    }

    public function test_product_image_is_empty_when_only_a_local_image_exists(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);
        $product = new Product;
        $product->setRawAttributes(['main_image' => 'uploads/local-product.jpg']);

        $this->assertNull($product->image);
    }
}