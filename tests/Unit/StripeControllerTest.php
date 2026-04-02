<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\StripeController;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\StripePaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class StripeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Model::reguard();

        parent::tearDown();
    }

    public function test_check_delegates_to_stripe_payment_service(): void
    {
        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('handleSourceCheck')
            ->once()
            ->with('STRIPE-ORDER-001', 'src_test_001')
            ->andReturn('success');
        $this->app->instance(StripePaymentService::class, $mock);

        $request = Request::create('/pay/stripe/check', 'GET', [
            'orderid' => 'STRIPE-ORDER-001',
            'source' => 'src_test_001',
        ]);

        $response = app(StripeController::class)->check($request);

        $this->assertSame('success', $response);
    }

    public function test_charge_returns_fail_when_order_is_missing(): void
    {
        $request = Request::create('/pay/stripe/charge', 'GET', [
            'orderid' => 'MISSING-ORDER',
            'stripeToken' => 'tok_test_001',
        ]);

        $response = app(StripeController::class)->charge($request);

        $this->assertSame('fail', $response);
    }

    public function test_charge_delegates_to_stripe_payment_service_for_existing_order(): void
    {
        $order = $this->createStripeOrder('STRIPE-ORDER-CHARGE-001');

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('handleCardCharge')
            ->once()
            ->with($order->order_sn, 'tok_test_002', Mockery::type('float'))
            ->andReturn('success');
        $this->app->instance(StripePaymentService::class, $mock);

        $controller = new class extends StripeController {
            public function getUsdCurrency($cny)
            {
                return 1.30;
            }
        };

        $request = Request::create('/pay/stripe/charge', 'GET', [
            'orderid' => $order->order_sn,
            'stripeToken' => 'tok_test_002',
        ]);

        $response = $controller->charge($request);

        $this->assertSame('success', $response);
    }

    private function createStripeOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Stripe Controller Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Stripe Controller Product ' . $orderSn,
            'gd_description' => 'Stripe Controller Product Description',
            'gd_keywords' => 'stripe,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Stripe Controller ' . $orderSn,
            'pay_check' => 'stripe-controller-' . strtolower($orderSn),
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
            'title' => 'Stripe Controller Product x 1',
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
