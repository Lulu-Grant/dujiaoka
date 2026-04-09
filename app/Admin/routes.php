<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->resource('goods', 'GoodsController');
    $router->resource('goods-group', 'GoodsGroupController');
    $router->resource('carmis', 'CarmisController');
    $router->resource('coupon', 'CouponController');
    $router->resource('emailtpl', 'EmailtplController');
    $router->resource('pay', 'PayController');
    $router->resource('order', 'OrderController');
    $router->get('v2/goods-group', [\App\Http\Controllers\AdminShell\GoodsGroupShellController::class, 'index'])->name('admin-shell.goods-group.index');
    $router->get('v2/goods-group/{id}', [\App\Http\Controllers\AdminShell\GoodsGroupShellController::class, 'show'])->name('admin-shell.goods-group.show');
    $router->get('v2/emailtpl', [\App\Http\Controllers\AdminShell\EmailTemplateShellController::class, 'index'])->name('admin-shell.emailtpl.index');
    $router->get('v2/emailtpl/{id}', [\App\Http\Controllers\AdminShell\EmailTemplateShellController::class, 'show'])->name('admin-shell.emailtpl.show');
    $router->get('import-carmis', 'CarmisController@importCarmis');
    $router->get('system-setting', 'SystemSettingController@systemSetting');
    $router->get('email-test', 'EmailTestController@emailTest');
});
