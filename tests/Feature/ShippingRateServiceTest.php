<?php

namespace Tests\Feature;

use App\Models\ShippingCarrier;
use App\Models\ShippingZone;
use App\Models\ShippingZoneRate;
use App\Http\Controllers\Admin\ShippingSettingsController;
use App\Services\ShippingRateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShippingRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('shipping_zone_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_carriers');

        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('delivery_estimate')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->default('*');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shipping_zone_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_zone_id');
            $table->unsignedBigInteger('shipping_carrier_id');
            $table->decimal('fee', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['shipping_zone_id', 'shipping_carrier_id']);
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('shipping_zone_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_carriers');

        parent::tearDown();
    }

    public function test_most_specific_matching_zone_supplies_checkout_rates(): void
    {
        $carrier = ShippingCarrier::create(['name' => 'FastShip', 'is_active' => true]);
        $this->addRate(
            ShippingZone::create(['name' => 'All locations', 'country' => '*', 'is_active' => true]),
            $carrier,
            30
        );
        $specificRate = $this->addRate(
            ShippingZone::create([
                'name' => 'Austin',
                'country' => 'United States',
                'state' => 'Texas',
                'city' => 'Austin',
                'is_active' => true,
            ]),
            $carrier,
            8.5
        );

        $rates = app(ShippingRateService::class)->availableRatesFor([
            'country' => ' united states ',
            'state' => 'TEXAS',
            'city' => 'austin',
        ]);

        $this->assertCount(1, $rates);
        $this->assertSame($specificRate->id, $rates->first()->id);
        $this->assertSame('8.50', $rates->first()->fee);
    }

    public function test_zone_without_an_active_carrier_rate_falls_back_to_a_less_specific_zone(): void
    {
        $activeCarrier = ShippingCarrier::create(['name' => 'Active carrier', 'is_active' => true]);
        $inactiveCarrier = ShippingCarrier::create(['name' => 'Inactive carrier', 'is_active' => false]);
        $fallbackRate = $this->addRate(
            ShippingZone::create(['name' => 'Country', 'country' => 'United States', 'is_active' => true]),
            $activeCarrier,
            18
        );
        $this->addRate(
            ShippingZone::create([
                'name' => 'City without active carrier',
                'country' => 'United States',
                'state' => 'Texas',
                'city' => 'Austin',
                'is_active' => true,
            ]),
            $inactiveCarrier,
            1
        );

        $rates = app(ShippingRateService::class)->availableRatesFor([
            'country' => 'United States',
            'state' => 'Texas',
            'city' => 'Austin',
        ]);

        $this->assertCount(1, $rates);
        $this->assertSame($fallbackRate->id, $rates->first()->id);
    }

    public function test_deleting_a_zone_soft_deletes_its_rates(): void
    {
        $carrier = ShippingCarrier::create(['name' => 'FastShip', 'is_active' => true]);
        $zone = ShippingZone::create([
            'name' => 'United States',
            'country' => 'United States',
            'is_active' => true,
        ]);
        $rate = $this->addRate($zone, $carrier, 12);

        app(ShippingSettingsController::class)->destroyZone($zone);

        $this->assertSoftDeleted('shipping_zones', ['id' => $zone->id]);
        $this->assertSoftDeleted('shipping_zone_rates', ['id' => $rate->id]);
        $this->assertCount(0, app(ShippingRateService::class)->availableRatesFor([
            'country' => 'United States',
        ]));
    }

    private function addRate(ShippingZone $zone, ShippingCarrier $carrier, float $fee): ShippingZoneRate
    {
        return ShippingZoneRate::create([
            'shipping_zone_id' => $zone->id,
            'shipping_carrier_id' => $carrier->id,
            'fee' => $fee,
        ]);
    }
}
