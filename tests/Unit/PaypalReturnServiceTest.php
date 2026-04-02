<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\PaypalGatewayClientInterface;
use App\Service\PaypalReturnService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaypalReturnServiceTest extends TestCase
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

    public function test_handle_approved_return_returns_error_for_invalid_gateway(): void
    {
        $order = $this->createPaypalOrder('PAYPAL-RETURN-ERROR-001', '/pay/not-paypal');
        $service = app(PaypalReturnService::class);

        $this->assertSame('error', $service->handleApprovedReturn($order->order_sn, 'PAY-ID', 'PAYER-ID'));
    }

    public function test_handle_approved_return_completes_order_on_success(): void
    {
        $order = $this->createPaypalOrder('PAYPAL-RETURN-SUCCESS-001');

        $sdkService = \Mockery::mock(PaypalGatewayClientInterface::class);
        $sdkService->shouldReceive('makeApiContext')
            ->once()
            ->andReturn(\Mockery::mock(\PayPal\Rest\ApiContext::class));
        $sdkService->shouldReceive('loadPayment')
            ->once()
            ->with('PAY-ID-SUCCESS', \Mockery::type(\PayPal\Rest\ApiContext::class))
            ->andReturn(\Mockery::mock(\PayPal\Api\Payment::class));
        $sdkService->shouldReceive('executeApprovedPayment')
            ->once()
            ->with(\Mockery::type(\PayPal\Api\Payment::class), 'PAYER-ID-SUCCESS', \Mockery::type(\PayPal\Rest\ApiContext::class));
        app()->instance(PaypalGatewayClientInterface::class, $sdkService);

        $service = app(PaypalReturnService::class);

        $response = $service->handleApprovedReturn($order->order_sn, 'PAY-ID-SUCCESS', 'PAYER-ID-SUCCESS');

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('PAY-ID-SUCCESS', $order->trade_no);
    }

    private function createPaypalOrder(string $orderSn, string $handlerRoute = '/pay/paypal'): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Paypal Service Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Paypal Service Product ' . $orderSn,
            'gd_description' => 'Paypal Service Product Description',
            'gd_keywords' => 'paypal,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Paypal Service ' . $orderSn,
            'pay_check' => 'paypal-service-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_key' => 'client-id',
            'merchant_pem' => 'client-secret',
            'pay_handleroute' => $handlerRoute,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Paypal Service Product x 1',
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
