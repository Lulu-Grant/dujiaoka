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

        $routes = collect($router->getRoutes()->getRoutes())
            ->map(function ($route) {
                return [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'name' => $route->getName(),
                ];
            })
            ->all();

        $this->assertRouteExists($routes, 'v2/goods-group', 'GET', 'admin-shell.goods-group.index');
        $this->assertRouteExists($routes, 'v2/goods-group/create', 'GET', 'admin-shell.goods-group.create');
        $this->assertRouteExists($routes, 'v2/order/batch-reset-search-pwd', 'GET', 'admin-shell.order.batch-reset-search-pwd');
        $this->assertRouteExists($routes, 'v2/order/batch-reset-search-pwd', 'POST', 'admin-shell.order.batch-reset-search-pwd.update');
        $this->assertRouteExists($routes, 'v2/emailtpl/{id}/edit', 'POST', 'admin-shell.emailtpl.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-status', 'GET', 'admin-shell.pay.batch-status');
        $this->assertRouteExists($routes, 'v2/pay/{id}/edit', 'POST', 'admin-shell.pay.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-status', 'POST', 'admin-shell.coupon.batch-status.update');
        $this->assertRouteExists($routes, 'v2/carmis/import', 'GET', 'admin-shell.carmis.import');
        $this->assertRouteExists($routes, 'v2/system-setting/base', 'POST', 'admin-shell.system-setting.base.update');
        $this->assertRouteExists($routes, 'v2/email-test/send', 'POST', 'admin-shell.email-test.send.store');
    }

    /**
     * @param array<int, array{uri:string,methods:array<int, string>,name:?string}> $routes
     */
    private function assertRouteExists(array $routes, string $uri, string $method, string $name): void
    {
        $this->assertTrue(
            collect($routes)->contains(function (array $route) use ($uri, $method, $name) {
                return $route['uri'] === $uri
                    && in_array($method, $route['methods'], true)
                    && $route['name'] === $name;
            }),
            sprintf('Route %s [%s] with name %s was not registered.', $uri, $method, $name)
        );
    }
}
