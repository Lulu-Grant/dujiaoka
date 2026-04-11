<?php

namespace Tests\Unit;

use App\Service\AdminShellRouteRegistrar;
use Illuminate\Routing\Router;
use Tests\TestCase;

class AdminShellRouteRegistrarTest extends TestCase
{
    public function test_register_adds_index_and_show_routes_for_registered_resources(): void
    {
        $router = app(Router::class);
        $registrar = app(AdminShellRouteRegistrar::class);

        $registrar->register($router);

        $uris = collect($router->getRoutes()->getRoutes())
            ->map(function ($route) {
                return $route->uri();
            })
            ->all();

        $this->assertContains('v2/goods-group', $uris);
        $this->assertContains('v2/emailtpl', $uris);
        $this->assertContains('v2/pay/{id}', $uris);
    }
}
