<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\StripeGatewayClientInterface;
use App\Service\StripePaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StripePaymentServiceTest extends TestCase
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

    public function test_handle_source_check_completes_consumed_source(): void
    {
        $order = $this->createStripeOrder('STRIPE-CHECK-001');

        $sdk = \Mockery::mock(StripeGatewayClientInterface::class);
        $sdk->shouldReceive('setApiKey')->once()->with('stripe-secret-key');
        $sdk->shouldReceive('retrieveSource')->once()->with('SRC-CONSUMED-001')->andReturn((object) [
            'status' => 'consumed',
            'id' => 'SRC-CONSUMED-001',
            'owner' => (object) ['name' => 'STRIPE-CHECK-001'],
        ]);
        app()->instance(StripeGatewayClientInterface::class, $sdk);

        $service = app(StripePaymentService::class);

        $response = $service->handleSourceCheck($order->order_sn, 'SRC-CONSUMED-001');

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('SRC-CONSUMED-001', $order->trade_no);
    }

    public function test_handle_card_charge_completes_successful_charge(): void
    {
        $order = $this->createStripeOrder('STRIPE-CHARGE-001');

        $sdk = \Mockery::mock(StripeGatewayClientInterface::class);
        $sdk->shouldReceive('setApiKey')->once()->with('stripe-secret-key');
        $sdk->shouldReceive('createCharge')->once()->with([
            'amount' => 130,
            'currency' => 'usd',
            'source' => 'tok_success',
        ])->andReturn((object) ['status' => 'succeeded']);
        app()->instance(StripeGatewayClientInterface::class, $sdk);

        $service = app(StripePaymentService::class);

        $response = $service->handleCardCharge($order->order_sn, 'tok_success', 130);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('tok_success', $order->trade_no);
    }

    public function test_handle_card_charge_reads_configured_target_currency(): void
    {
        config(['dujiaoka.stripe_target_currency' => 'EUR']);
        $order = $this->createStripeOrder('STRIPE-CHARGE-CURRENCY-001');

        $sdk = \Mockery::mock(StripeGatewayClientInterface::class);
        $sdk->shouldReceive('setApiKey')->once()->with('stripe-secret-key');
        $sdk->shouldReceive('createCharge')->once()->with([
            'amount' => 130,
            'currency' => 'eur',
            'source' => 'tok_currency',
        ])->andReturn((object) ['status' => 'pending']);
        app()->instance(StripeGatewayClientInterface::class, $sdk);

        $response = app(StripePaymentService::class)->handleCardCharge($order->order_sn, 'tok_currency', 130);

        $this->assertSame('pending', $response);
    }

    private function createStripeOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Stripe Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Stripe Product ' . $orderSn,
            'gd_description' => 'Stripe Product Description',
            'gd_keywords' => 'stripe,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Stripe ' . $orderSn,
            'pay_check' => 'stripe-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'stripe-public-key',
            'merchant_pem' => 'stripe-secret-key',
            'pay_handleroute' => '/pay/stripe',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Stripe Product x 1',
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
