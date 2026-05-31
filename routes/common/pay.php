<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */
use Illuminate\Support\Facades\Route;

Route::get('pay-gateway/{handle}/{payway}/{orderSN}', 'PayController@redirectGateway');

// 支付相关
Route::group(['prefix' => 'pay', 'namespace' => 'Pay', 'middleware' => ['dujiaoka.pay_gate_way']], function () {
    // 支付宝
    Route::get('alipay/{payway}/{orderSN}', 'AlipayController@gateway');
    Route::post('alipay/notify_url', 'AlipayController@notifyUrl');
    // 微信
    Route::get('wepay/{payway}/{orderSN}', 'WepayController@gateway');
    Route::post('wepay/notify_url', 'WepayController@notifyUrl');
    // 易支付
    Route::get('yipay/{payway}/{orderSN}', 'YipayController@gateway');
    Route::get('yipay/notify_url', 'YipayController@notifyUrl');
    Route::get('yipay/return_url', 'YipayController@returnUrl')->name('yipay-return');
    // epusdt
    Route::get('epusdt/{payway}/{orderSN}', 'EpusdtController@gateway');
    Route::post('epusdt/notify_url', 'EpusdtController@notifyUrl');
    Route::get('epusdt/return_url', 'EpusdtController@returnUrl')->name('epusdt-return');

});
