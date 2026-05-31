<?php

use App\Models\Pay;
use Illuminate\Database\Seeder;

class PaySampleSeeder extends Seeder
{
    /**
     * Seed optional payment method examples for local development.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->examples() as $example) {
            Pay::withTrashed()->updateOrCreate(
                ['pay_check' => $example['pay_check']],
                array_merge($example, ['deleted_at' => null])
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function examples(): array
    {
        return [
            [
                'pay_name' => '支付宝当面付',
                'pay_check' => 'zfbf2f',
                'pay_method' => 2,
                'pay_client' => 3,
                'merchant_id' => '商户号',
                'merchant_key' => '支付宝公钥',
                'merchant_pem' => '商户私钥',
                'pay_handleroute' => '/pay/alipay',
                'is_open' => 1,
            ],
            [
                'pay_name' => '支付宝 PC',
                'pay_check' => 'aliweb',
                'pay_method' => 1,
                'pay_client' => 1,
                'merchant_id' => '商户号',
                'merchant_key' => '',
                'merchant_pem' => '密钥',
                'pay_handleroute' => '/pay/alipay',
                'is_open' => 1,
            ],
            [
                'pay_name' => '微信扫码',
                'pay_check' => 'wescan',
                'pay_method' => 2,
                'pay_client' => 1,
                'merchant_id' => '商户号',
                'merchant_key' => '',
                'merchant_pem' => '密钥',
                'pay_handleroute' => '/pay/wepay',
                'is_open' => 1,
            ],
            [
                'pay_name' => '易支付-支付宝',
                'pay_check' => 'alipay',
                'pay_method' => 1,
                'pay_client' => 1,
                'merchant_id' => '商户号',
                'merchant_key' => '',
                'merchant_pem' => '密钥',
                'pay_handleroute' => '/pay/yipay',
                'is_open' => 2,
            ],
            [
                'pay_name' => '易支付-微信',
                'pay_check' => 'wxpay',
                'pay_method' => 1,
                'pay_client' => 1,
                'merchant_id' => '商户号',
                'merchant_key' => null,
                'merchant_pem' => '密钥',
                'pay_handleroute' => '/pay/yipay',
                'is_open' => 1,
            ],
            [
                'pay_name' => '易支付-QQ 钱包',
                'pay_check' => 'qqpay',
                'pay_method' => 1,
                'pay_client' => 1,
                'merchant_id' => '商户号',
                'merchant_key' => null,
                'merchant_pem' => '密钥',
                'pay_handleroute' => '/pay/yipay',
                'is_open' => 1,
            ],
            [
                'pay_name' => 'Epusdt[trc20]',
                'pay_check' => 'epusdt',
                'pay_method' => 1,
                'pay_client' => 3,
                'merchant_id' => 'API密钥',
                'merchant_key' => '不填即可',
                'merchant_pem' => 'api请求地址',
                'pay_handleroute' => '/pay/epusdt',
                'is_open' => 0,
            ],
        ];
    }
}
