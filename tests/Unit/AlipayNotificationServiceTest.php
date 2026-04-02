<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\AlipayNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AlipayNotificationServiceTest extends TestCase
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

    public function test_handle_notification_returns_error_when_order_is_missing(): void
    {
        $service = new class extends AlipayNotificationService {
            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
            {
                return new \stdClass();
            }
        };

        $this->assertSame('error', $service->handleNotification('MISSING-ALIPAY-ORDER'));
    }

    public function test_handle_notification_completes_order_after_successful_verification(): void
    {
        $order = $this->createAlipayOrder('ALIPAY-SUCCESS-001');

        $service = new class extends AlipayNotificationService {
            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'trade_status' => 'TRADE_SUCCESS',
                    'out_trade_no' => 'ALIPAY-SUCCESS-001',
                    'total_amount' => '10.00',
                    'trade_no' => 'ALIPAY-TRADE-001',
                ];
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('ALIPAY-TRADE-001', $order->trade_no);
    }

    private function createAlipayOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Alipay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Alipay Product ' . $orderSn,
            'gd_description' => 'Alipay Product Description',
            'gd_keywords' => 'alipay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Alipay ' . $orderSn,
            'pay_check' => 'alipay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'alipay-app-id',
            'merchant_key' => 'alipay-public-key',
            'merchant_pem' => 'alipay-private-key',
            'pay_handleroute' => '/pay/alipay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Alipay Product x 1',
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
