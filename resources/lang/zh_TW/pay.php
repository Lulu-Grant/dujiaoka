<?php

return [
    'labels' => [
        'Pay' => '支付通道',
        'pay' => '支付通道',
    ],
    'fields' => [
        'merchant_id' => '商戶 ID',
        'merchant_key' => '商戶 KEY',
        'merchant_pem' => '商戶金鑰',
        'pay_check' => '支付標識',
        'pay_client' => '支付場景',
        'pay_handleroute' => '支付處理路由',
        'pay_method' => '支付方式',
        'pay_name' => '支付名稱',
        'is_open' => '是否啟用',
        'method_jump' => '跳躍',
        'method_scan' => '掃碼',
        'pay_client_pc' => '計算機PC',
        'pay_client_mobile' => '行動電話',
        'pay_client_all' => '通用',
        'lifecycle' => '生命週期',
        'lifecycle_active' => '新版本維護中',
        'lifecycle_legacy' => '遺留待替換',
        'lifecycle_retired' => '已退役',
        'pay_check_help' => '請避免繼續使用已退役通道標識；PayPal 與 Stripe 目前屬於遺留待替換通道。',
    ],
    'options' => [
    ],
];
