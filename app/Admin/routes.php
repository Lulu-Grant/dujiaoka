<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;
use App\Service\LegacyAdminShellRedirectService;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', function () {
        return app(LegacyAdminShellRedirectService::class)->toDashboard();
    });
    $router->get('v2/dashboard', [\App\Http\Controllers\AdminShell\DashboardShellController::class, 'index']);
    app(\App\Service\AdminShellRouteRegistrar::class)->register($router);

    foreach ([
        'goods' => ['index', 'create', 'show', 'edit'],
        'goods-group' => ['index', 'create', 'show', 'edit'],
        'carmis' => ['index', 'create', 'show', 'edit'],
        'coupon' => ['index', 'create', 'show', 'edit'],
        'emailtpl' => ['index', 'create', 'show', 'edit'],
        'pay' => ['index', 'create', 'show', 'edit'],
        'order' => ['index', 'show', 'edit'],
    ] as $resource => $actions) {
        if (in_array('index', $actions, true)) {
            $router->get($resource, function () use ($resource) {
                return app(LegacyAdminShellRedirectService::class)->toResourceIndex($resource);
            })->name($resource.'.index');
        }

        if (in_array('create', $actions, true)) {
            $router->get($resource.'/create', function () use ($resource) {
                return app(LegacyAdminShellRedirectService::class)->toResourceCreate($resource);
            })->name($resource.'.create');
        }

        if (in_array('show', $actions, true)) {
            $router->get($resource.'/{id}', function ($id) use ($resource) {
                return app(LegacyAdminShellRedirectService::class)->toResourceShow($resource, $id);
            })->name($resource.'.show');
        }

        if (in_array('edit', $actions, true)) {
            $router->get($resource.'/{id}/edit', function ($id) use ($resource) {
                return app(LegacyAdminShellRedirectService::class)->toResourceEdit($resource, $id);
            })->name($resource.'.edit');
        }
    }

    $router->get('import-carmis', function () {
        return app(LegacyAdminShellRedirectService::class)->toCarmiImport();
    });

    $router->get('system-setting', function () {
        return app(LegacyAdminShellRedirectService::class)->toSystemSetting();
    });

    $router->get('email-test', function () {
        return app(LegacyAdminShellRedirectService::class)->toEmailTest();
    });
});
