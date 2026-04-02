<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

return [
    'dujiaoka_version' => '2.0.6',
    'async_side_effects' => env('DUJIAOKA_ASYNC_SIDE_EFFECTS', false),
    'paypal_mode' => env('DUJIAOKA_PAYPAL_MODE', 'live'),
    'paypal_source_currency' => env('DUJIAOKA_PAYPAL_SOURCE_CURRENCY', 'CNY'),
    'paypal_target_currency' => env('DUJIAOKA_PAYPAL_TARGET_CURRENCY', 'USD'),
    'stripe_source_currency' => env('DUJIAOKA_STRIPE_SOURCE_CURRENCY', 'CNY'),
    'stripe_target_currency' => env('DUJIAOKA_STRIPE_TARGET_CURRENCY', 'USD'),
    // 模板集合
    'templates' => [
        'avatar' => 'Avatar[modernized-default]',
    ],
    // 语言
    'language' => [
        'zh_CN' => '简体中文',
        'zh_TW' => '繁体中文',
    ],
];
