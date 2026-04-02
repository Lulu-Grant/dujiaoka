<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\PaypalCheckoutService;
use App\Service\PaypalSdkService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaypalCheckoutServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_create_approval_url_returns_payment_link(): void
    {
        [$order, $payGateway] = $this->createPaypalContext('PAYPAL-CHECKOUT-001');

        $sdkService = \Mockery::mock(PaypalSdkService::class);
        $sdkService->shouldReceive('makeApiContext')
            ->once()
            ->andReturn(\Mockery::mock(\PayPal\Rest\ApiContext::class));
        $sdkService->shouldReceive('createApprovalLink')
            ->once()
            ->with($order, 1.23, \Mockery::type(\PayPal\Rest\ApiContext::class))
            ->andReturn('https://paypal.example.com/approval');
        app()->instance(PaypalSdkService::class, $sdkService);

        $service = new class extends PaypalCheckoutService {
            protected function convertAmount(float $amount): float
            {
                return 1.23;
            }
        };

        $approvalUrl = $service->createApprovalUrl($order, $payGateway);

        $this->assertSame('https://paypal.example.com/approval', $approvalUrl);
    }

    private function createPaypalContext(string $orderSn): array
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Paypal Checkout Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Paypal Checkout Product ' . $orderSn,
            'gd_description' => 'Paypal Checkout Product Description',
            'gd_keywords' => 'paypal,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Paypal Checkout ' . $orderSn,
            'pay_check' => 'paypal-checkout-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_key' => 'client-id',
            'merchant_pem' => 'client-secret',
            'pay_handleroute' => '/pay/paypal',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Paypal Checkout Product x 1',
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

        return [$order, $pay];
    }
}
