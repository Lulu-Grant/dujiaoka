<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\PaymentCallbackService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentCallbackServiceTest extends TestCase
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

    public function test_handle_signed_notification_returns_missing_context_response_when_order_is_invalid(): void
    {
        $response = app(PaymentCallbackService::class)->handleSignedNotification(
            'MISSING-ORDER',
            '/pay/test',
            function () {
                return true;
            },
            10.00,
            'TRADE-001',
            'error',
            'fail',
            'success'
        );

        $this->assertSame('error', $response);
    }

    public function test_handle_signed_notification_completes_order_when_signature_is_valid(): void
    {
        $order = $this->createOrder('CALLBACK-SERVICE-001', '/pay/test');

        $response = app(PaymentCallbackService::class)->handleSignedNotification(
            $order->order_sn,
            '/pay/test',
            function () {
                return true;
            },
            10.00,
            'TRADE-CALLBACK-001',
            'error',
            'fail',
            'success'
        );

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-CALLBACK-001', $order->trade_no);
    }

    private function createOrder(string $orderSn, string $handlerRoute): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Callback Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Callback Product ' . $orderSn,
            'gd_description' => 'Callback Product Description',
            'gd_keywords' => 'callback,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Callback ' . $orderSn,
            'pay_check' => 'callback-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'callback-secret',
            'pay_handleroute' => $handlerRoute,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Callback Product x 1',
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
