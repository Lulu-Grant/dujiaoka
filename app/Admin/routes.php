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
    $router->get('v2/goods-group/create', [\App\Http\Controllers\AdminShell\GoodsGroupActionController::class, 'create']);
    $router->post('v2/goods-group/create', [\App\Http\Controllers\AdminShell\GoodsGroupActionController::class, 'store']);
    $router->get('v2/goods-group/{id}/edit', [\App\Http\Controllers\AdminShell\GoodsGroupActionController::class, 'edit']);
    $router->post('v2/goods-group/{id}/edit', [\App\Http\Controllers\AdminShell\GoodsGroupActionController::class, 'update']);
    $router->get('v2/pay/create', [\App\Http\Controllers\AdminShell\PayActionController::class, 'create']);
    $router->post('v2/pay/create', [\App\Http\Controllers\AdminShell\PayActionController::class, 'store']);
    $router->get('v2/pay/{id}/edit', [\App\Http\Controllers\AdminShell\PayActionController::class, 'edit']);
    $router->post('v2/pay/{id}/edit', [\App\Http\Controllers\AdminShell\PayActionController::class, 'update']);
    $router->get('v2/emailtpl/create', [\App\Http\Controllers\AdminShell\EmailTemplateActionController::class, 'create']);
    $router->post('v2/emailtpl/create', [\App\Http\Controllers\AdminShell\EmailTemplateActionController::class, 'store']);
    $router->get('v2/emailtpl/{id}/edit', [\App\Http\Controllers\AdminShell\EmailTemplateActionController::class, 'edit']);
    $router->post('v2/emailtpl/{id}/edit', [\App\Http\Controllers\AdminShell\EmailTemplateActionController::class, 'update']);
    $router->get('v2/coupon/create', [\App\Http\Controllers\AdminShell\CouponActionController::class, 'create']);
    $router->post('v2/coupon/create', [\App\Http\Controllers\AdminShell\CouponActionController::class, 'store']);
    $router->get('v2/coupon/{id}/edit', [\App\Http\Controllers\AdminShell\CouponActionController::class, 'edit']);
    $router->post('v2/coupon/{id}/edit', [\App\Http\Controllers\AdminShell\CouponActionController::class, 'update']);
    $router->get('v2/carmis/import', [\App\Http\Controllers\AdminShell\CarmiImportActionController::class, 'create']);
    $router->post('v2/carmis/import', [\App\Http\Controllers\AdminShell\CarmiImportActionController::class, 'store']);
    $router->get('v2/system-setting/base', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editBase']);
    $router->post('v2/system-setting/base', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updateBase']);
    $router->get('v2/system-setting/mail', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editMail']);
    $router->post('v2/system-setting/mail', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updateMail']);
    $router->get('v2/system-setting/push', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editPush']);
    $router->post('v2/system-setting/push', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updatePush']);
    $router->get('v2/system-setting/experience', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'editExperience']);
    $router->post('v2/system-setting/experience', [\App\Http\Controllers\AdminShell\SystemSettingActionController::class, 'updateExperience']);
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
