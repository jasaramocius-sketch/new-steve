<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use Tests\TestCase;

class ProductImportPolicyReviewsTest extends TestCase
{
    public function test_import_data_normalizes_policy_and_reviews_columns(): void
    {
        $data = [
            'description' => 'High quality replacement part.',
            'policy_text' => '<p>30-day return policy</p>',
            'reviews_data' => '[{"name":"Jamie","rating":5,"text":"Excellent product","deleted":false}]',
            'features' => "Premium quality\nEasy installation\nTrusted fit",
        ];

        $normalized = ProductController::normalizeImportedProductData($data);

        $this->assertSame("<p>High quality replacement part.</p>\n", $normalized['description']);
        $this->assertSame('<p>30-day return policy</p>', $normalized['policy_text']);
        $this->assertSame('Jamie', $normalized['reviews_data'][0]['name']);
        $this->assertSame(5, $normalized['reviews_data'][0]['rating']);
        $this->assertSame(['Premium quality', 'Easy installation', 'Trusted fit'], $normalized['features']);
    }

    public function test_import_data_accepts_alternative_policy_and_reviews_names(): void
    {
        $data = [
            'description' => 'Ford compatible item.',
            'buy_return_policy' => '<p>Free returns within 14 days.</p>',
            'reviews' => 'Alice::5::Great quality|Bob::4::Worked perfectly',
        ];

        $normalized = ProductController::normalizeImportedProductData($data);

        $this->assertSame('<p>Free returns within 14 days.</p>', $normalized['policy_text']);
        $this->assertCount(2, $normalized['reviews_data']);
        $this->assertSame('Alice', $normalized['reviews_data'][0]['name']);
        $this->assertSame('Bob', $normalized['reviews_data'][1]['name']);
    }
}
