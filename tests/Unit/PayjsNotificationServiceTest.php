<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\PayjsNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PayjsNotificationServiceTest extends TestCase
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
        $service = new class extends PayjsNotificationService {
            protected function getNotifyInfo(): array
            {
                return [];
            }
        };

        $this->assertSame('error', $service->handleNotification('MISSING-PAYJS-ORDER'));
    }

    public function test_handle_notification_completes_order_with_notify_payload(): void
    {
        $order = $this->createPayjsOrder('PAYJS-SUCCESS-001');

        $service = new class extends PayjsNotificationService {
            protected function getNotifyInfo(): array
            {
                return [
                    'out_trade_no' => 'PAYJS-SUCCESS-001',
                    'total_fee' => '1000',
                    'payjs_order_id' => 'PAYJS-TRADE-001',
                ];
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('PAYJS-TRADE-001', $order->trade_no);
    }

    private function createPayjsOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Payjs Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Payjs Product ' . $orderSn,
            'gd_description' => 'Payjs Product Description',
            'gd_keywords' => 'payjs,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Payjs ' . $orderSn,
            'pay_check' => 'payjs-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'payjs-mch',
            'merchant_pem' => 'payjs-secret',
            'pay_handleroute' => '/pay/payjs',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Payjs Product x 1',
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
