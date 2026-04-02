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
    // 码支付
    Route::get('mapay/{payway}/{orderSN}', 'MapayController@gateway');
    Route::post('mapay/notify_url', 'MapayController@notifyUrl');
    // 易支付
    Route::get('yipay/{payway}/{orderSN}', 'YipayController@gateway');
    Route::get('yipay/notify_url', 'YipayController@notifyUrl');
    Route::get('yipay/return_url', 'YipayController@returnUrl')->name('yipay-return');
    // paypal
    Route::get('paypal/{payway}/{orderSN}', 'PaypalPayController@gateway');
    Route::get('paypal/return_url', 'PaypalPayController@returnUrl')->name('paypal-return');
    Route::any('paypal/notify_url', 'PaypalPayController@notifyUrl');
    // stripe
    Route::get('stripe/{payway}/{oid}','StripeController@gateway');
    Route::get('stripe/return_url','StripeController@returnUrl');
    Route::get('stripe/check','StripeController@check');
    Route::get('stripe/charge','StripeController@charge');
    // Coinbase
    Route::get('coinbase/{payway}/{orderSN}', 'CoinbaseController@gateway');
    Route::post('coinbase/notify_url', 'CoinbaseController@notifyUrl');
    // epusdt
    Route::get('epusdt/{payway}/{orderSN}', 'EpusdtController@gateway');
    Route::post('epusdt/notify_url', 'EpusdtController@notifyUrl');
    Route::get('epusdt/return_url', 'EpusdtController@returnUrl')->name('epusdt-return');
    // tokenpay
    Route::get('tokenpay/{payway}/{orderSN}', 'TokenPayController@gateway');
    Route::post('tokenpay/notify_url', 'TokenPayController@notifyUrl');
    Route::get('tokenpay/return_url', 'TokenPayController@returnUrl')->name('tokenpay-return');

});
