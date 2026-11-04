<?php

namespace Tests\Unit;

use App\Models\Image;
use Tests\TestCase;

class ImageKeyTest extends TestCase
{
    public function test_local_paths_are_normalised(): void
    {
        $this->assertSame('uploads/2026/10/abc.webp', Image::imageKey('uploads/2026/10/abc.webp'));
        $this->assertSame('uploads/2026/10/abc.webp', Image::imageKey('storage/uploads/2026/10/abc.webp'));
        $this->assertSame('uploads/2026/10/abc.webp', Image::imageKey('public/uploads/2026/10/abc.webp'));
        $this->assertSame('uploads/2026/10/abc.webp', Image::imageKey('/uploads/2026/10/abc.webp'));
    }

    public function test_hub_references_resolve_to_the_object_key(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);

        $key = 'steveautoparts/images/0B-BBR/0B_0011023148_Fro.jpg';

        $this->assertSame($key, Image::imageKey('s3://autoparts-hub/'.$key));
        $this->assertSame($key, Image::imageKey('https://autoparts-hub.s3.amazonaws.com/'.$key));
        $this->assertSame($key, Image::imageKey('https://autoparts-hub.s3.us-east-1.amazonaws.com/'.$key));
        $this->assertSame($key, Image::imageKey($key));
    }

    public function test_references_outside_the_hub_bucket_are_not_tracked(): void
    {
        config(['filesystems.disks.source_images.bucket' => 'autoparts-hub']);

        $this->assertNull(Image::imageKey('https://example.com/uploads/abc.webp'));
        $this->assertNull(Image::imageKey('s3://another-bucket/uploads/abc.webp'));
        $this->assertNull(Image::imageKey(''));
        $this->assertNull(Image::imageKey(null));
    }
}
