<?php

namespace Tests\Unit;

use Tests\TestCase;

class BuyerPriceTest extends TestCase
{
    public function test_global_commission_is_added_to_base_price(): void
    {
        $this->assertSame(110.0, calculateBuyerPrice(100, 10));
    }

    public function test_commission_amount_is_rounded_to_cents(): void
    {
        $this->assertSame(10.01, calculateBuyerPrice(9.99, 0.25));
    }

    public function test_zero_commission_preserves_base_price(): void
    {
        $this->assertSame(19.99, calculateBuyerPrice(19.99, 0));
    }
}