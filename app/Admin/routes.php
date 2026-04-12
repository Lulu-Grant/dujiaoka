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
    $router->get('v2/email-test/send', [\App\Http\Controllers\AdminShell\EmailTestActionController::class, 'create']);
    $router->post('v2/email-test/send', [\App\Http\Controllers\AdminShell\EmailTestActionController::class, 'store']);
    $router->get('v2/carmis/import', [\App\Http\Controllers\AdminShell\CarmiImportActionController::class, 'create']);
    $router->post('v2/carmis/import', [\App\Http\Controllers\AdminShell\CarmiImportActionController::class, 'store']);
    $router->get('v2/system-setting/base', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editBase']);
    $router->post('v2/system-setting/base', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updateBase']);
    $router->get('v2/system-setting/mail', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editMail']);
    $router->post('v2/system-setting/mail', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updateMail']);
    $router->get('v2/system-setting/push', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editPush']);
    $router->post('v2/system-setting/push', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updatePush']);
    $router->resource('goods', 'GoodsController');
    $router->resource('goods-group', 'GoodsGroupController');
    $router->resource('carmis', 'CarmisController');
    $router->resource('coupon', 'CouponController');
    $router->resource('emailtpl', 'EmailtplController');
    $router->resource('pay', 'PayController');
    $router->resource('order', 'OrderController');
    app(\App\Service\AdminShellRouteRegistrar::class)->register($router);
    $router->get('import-carmis', 'CarmisController@importCarmis');
    $router->get('system-setting', 'SystemSettingController@systemSetting');
    $router->get('email-test', 'EmailTestController@emailTest');
});
