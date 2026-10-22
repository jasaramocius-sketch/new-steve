<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class AdminProductRoutesTest extends TestCase
{
    public function test_import_route_is_not_shadowed_by_product_id_route(): void
    {
        $router = app('router');
        $route = $router->getRoutes()->match(Request::create('/admin/products/import', 'GET'));

        $this->assertNotNull($route);
        $this->assertSame('admin.products.import-form', $route->getName());
    }
}
