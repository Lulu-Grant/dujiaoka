<?php

namespace Tests\Unit;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PayControllerTest extends TestCase
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

    public function test_load_gateway_assigns_matching_gateway_to_order(): void
    {
        [$order, $pay] = $this->createOrderAndPay(Order::STATUS_WAIT_PAY, 'pay-check-ok');

        $controller = app(PayController::class);
        $controller->loadGateWay($order->order_sn, $pay->pay_check);

        $order->refresh();

        $this->assertSame($pay->id, $order->pay_id);
    }

    public function test_check_order_rejects_already_processed_order(): void
    {
        [$order] = $this->createOrderAndPay(Order::STATUS_PENDING, 'pay-check-pending');

        $this->expectException(RuleValidationException::class);

        app(PayController::class)->checkOrder($order->order_sn);
    }

    public function test_load_gateway_rejects_unknown_gateway(): void
    {
        [$order] = $this->createOrderAndPay(Order::STATUS_WAIT_PAY, 'pay-check-missing');

        $this->expectException(RuleValidationException::class);

        app(PayController::class)->loadGateWay($order->order_sn, 'missing-gateway');
    }

    public function test_redirect_gateway_completes_free_orders_without_external_gateway_jump(): void
    {
        [$order] = $this->createOrderAndPay(Order::STATUS_WAIT_PAY, 'pay-check-free', 0.00);

        $response = app(PayController::class)->redirectGateway('pay/test', 'pay-check-free', $order->order_sn);

        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertStringContainsString('detail-order-sn', $response->getTargetUrl());
    }

    private function createOrderAndPay(int $status, string $payCheck, float $actualPrice = 10.00): array
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Pay Group ' . $payCheck,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Pay Product ' . $payCheck,
            'gd_description' => 'Pay Product Description',
            'gd_keywords' => 'pay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Pay ' . $payCheck,
            'pay_check' => $payCheck,
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'secret',
            'pay_handleroute' => '/pay/test',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => strtoupper($payCheck) . '-ORDER',
            'goods_id' => $goods->id,
            'title' => 'Pay Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => $actualPrice,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => $status,
        ]);

        return [$order, $pay];
    }
}
