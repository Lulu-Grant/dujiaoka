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
        $this->assertRouteExists($routes, 'v2/goods/create', 'GET', 'admin-shell.goods.create');
        $this->assertRouteExists($routes, 'v2/goods/create', 'POST', 'admin-shell.goods.store');
        $this->assertRouteExists($routes, 'v2/order/batch-reset-search-pwd', 'GET', 'admin-shell.order.batch-reset-search-pwd');
        $this->assertRouteExists($routes, 'v2/order/batch-reset-search-pwd', 'POST', 'admin-shell.order.batch-reset-search-pwd.update');
        $this->assertRouteExists($routes, 'v2/order/batch-info', 'GET', 'admin-shell.order.batch-info');
        $this->assertRouteExists($routes, 'v2/order/batch-info', 'POST', 'admin-shell.order.batch-info.update');
        $this->assertRouteExists($routes, 'v2/order/batch-title', 'GET', 'admin-shell.order.batch-title');
        $this->assertRouteExists($routes, 'v2/order/batch-title', 'POST', 'admin-shell.order.batch-title.update');
        $this->assertRouteExists($routes, 'v2/order/batch-type', 'GET', 'admin-shell.order.batch-type');
        $this->assertRouteExists($routes, 'v2/order/batch-type', 'POST', 'admin-shell.order.batch-type.update');
        $this->assertRouteExists($routes, 'v2/emailtpl/{id}/edit', 'POST', 'admin-shell.emailtpl.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-client', 'GET', 'admin-shell.pay.batch-client');
        $this->assertRouteExists($routes, 'v2/pay/batch-client', 'POST', 'admin-shell.pay.batch-client.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-method', 'GET', 'admin-shell.pay.batch-method');
        $this->assertRouteExists($routes, 'v2/pay/batch-method', 'POST', 'admin-shell.pay.batch-method.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-name', 'GET', 'admin-shell.pay.batch-name');
        $this->assertRouteExists($routes, 'v2/pay/batch-name', 'POST', 'admin-shell.pay.batch-name.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-prefix', 'GET', 'admin-shell.pay.batch-name-prefix');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-prefix', 'POST', 'admin-shell.pay.batch-name-prefix.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-suffix', 'GET', 'admin-shell.pay.batch-name-suffix');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-suffix', 'POST', 'admin-shell.pay.batch-name-suffix.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-replace', 'GET', 'admin-shell.pay.batch-name-replace');
        $this->assertRouteExists($routes, 'v2/pay/batch-name-replace', 'POST', 'admin-shell.pay.batch-name-replace.update');
        $this->assertRouteExists($routes, 'v2/pay/batch-status', 'GET', 'admin-shell.pay.batch-status');
        $this->assertRouteExists($routes, 'v2/pay/{id}/edit', 'POST', 'admin-shell.pay.update');
        $this->assertRouteExists($routes, 'v2/order/batch-title-prefix', 'GET', 'admin-shell.order.batch-title-prefix');
        $this->assertRouteExists($routes, 'v2/order/batch-title-prefix', 'POST', 'admin-shell.order.batch-title-prefix.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-status', 'POST', 'admin-shell.coupon.batch-status.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-use', 'GET', 'admin-shell.coupon.batch-use');
        $this->assertRouteExists($routes, 'v2/coupon/batch-use', 'POST', 'admin-shell.coupon.batch-use.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-discount', 'GET', 'admin-shell.coupon.batch-discount');
        $this->assertRouteExists($routes, 'v2/coupon/batch-discount', 'POST', 'admin-shell.coupon.batch-discount.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-ret', 'GET', 'admin-shell.coupon.batch-ret');
        $this->assertRouteExists($routes, 'v2/coupon/batch-ret', 'POST', 'admin-shell.coupon.batch-ret.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code', 'GET', 'admin-shell.coupon.batch-code');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code', 'POST', 'admin-shell.coupon.batch-code.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code-prefix', 'GET', 'admin-shell.coupon.batch-code-prefix');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code-prefix', 'POST', 'admin-shell.coupon.batch-code-prefix.update');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code-suffix', 'GET', 'admin-shell.coupon.batch-code-suffix');
        $this->assertRouteExists($routes, 'v2/coupon/batch-code-suffix', 'POST', 'admin-shell.coupon.batch-code-suffix.update');
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
