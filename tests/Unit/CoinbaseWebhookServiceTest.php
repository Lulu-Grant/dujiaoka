<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\CoinbaseWebhookService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CoinbaseWebhookServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        config(['dujiaoka.async_side_effects' => true]);
        Queue::fake();
        Cache::put('system-setting', [
            'template' => 'hyper',
            'text_logo' => 'Test Shop',
            'manage_email' => 'admin@example.com',
            'is_open_server_jiang' => BaseModel::STATUS_CLOSE,
            'is_open_telegram_push' => BaseModel::STATUS_CLOSE,
            'is_open_bark_push' => BaseModel::STATUS_CLOSE,
            'is_open_qywxbot_push' => BaseModel::STATUS_CLOSE,
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_handle_webhook_rejects_invalid_signature(): void
    {
        $order = $this->createCoinbaseOrder('COINBASE-FAIL-001');
        $payload = $this->buildPayload($order->order_sn, 'COINBASE-TRADE-FAIL');

        $response = app(CoinbaseWebhookService::class)->handleWebhook($payload, 'invalid-signature');

        $this->assertSame('fail|wrong sig', $response);
    }

    public function test_handle_webhook_completes_order_with_valid_signature(): void
    {
        $order = $this->createCoinbaseOrder('COINBASE-SUCCESS-001');
        $payGateway = Pay::query()->findOrFail($order->pay_id);
        $payload = $this->buildPayload($order->order_sn, 'COINBASE-TRADE-SUCCESS');
        $signature = hash_hmac('sha256', $payload, $payGateway->merchant_pem);

        $response = app(CoinbaseWebhookService::class)->handleWebhook($payload, $signature);

        $order->refresh();

        $this->assertSame('{"status": 200}', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('COINBASE-TRADE-SUCCESS', $order->trade_no);
    }

    private function buildPayload(string $orderSn, string $tradeNo): string
    {
        return json_encode([
            'event' => [
                'data' => [
                    'code' => $tradeNo,
                    'metadata' => [
                        'customer_id' => $orderSn,
                        'customer_name' => 'Coinbase Order',
                    ],
                    'payments' => [
                        [
                            'status' => 'CONFIRMED',
                            'value' => [
                                'local' => [
                                    'amount' => '10.00',
                                    'currency' => 'CNY',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    private function createCoinbaseOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Coinbase Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Coinbase Product ' . $orderSn,
            'gd_description' => 'Coinbase Product Description',
            'gd_keywords' => 'coinbase,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Coinbase ' . $orderSn,
            'pay_check' => 'coinbase-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_key' => 'coinbase-api-key',
            'merchant_pem' => 'coinbase-webhook-secret',
            'pay_handleroute' => '/pay/coinbase',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Coinbase Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);
    }
}
