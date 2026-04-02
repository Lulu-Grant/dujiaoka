<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\WepayNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WepayNotificationServiceTest extends TestCase
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
        $service = new class extends WepayNotificationService {
            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }
        };

        $this->assertSame('error', $service->handleNotification('MISSING-WEPAY-ORDER'));
    }

    public function test_handle_notification_completes_order_after_successful_verification(): void
    {
        $order = $this->createWepayOrder('WEPAY-SUCCESS-001');

        $service = new class extends WepayNotificationService {
            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'out_trade_no' => 'WEPAY-SUCCESS-001',
                    'total_fee' => '1000',
                    'transaction_id' => 'WEPAY-TRADE-001',
                ];
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('WEPAY-TRADE-001', $order->trade_no);
    }

    private function createWepayOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Wepay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Wepay Product ' . $orderSn,
            'gd_description' => 'Wepay Product Description',
            'gd_keywords' => 'wepay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Wepay ' . $orderSn,
            'pay_check' => 'wepay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'wechat-app-id',
            'merchant_key' => 'wechat-mch-id',
            'merchant_pem' => 'wechat-key',
            'pay_handleroute' => '/pay/wepay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Wepay Product x 1',
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
