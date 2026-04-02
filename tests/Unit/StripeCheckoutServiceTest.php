<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\StripeCheckoutService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StripeCheckoutServiceTest extends TestCase
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

    public function test_build_checkout_view_data_contains_render_values(): void
    {
        [$order, $payGateway] = $this->createStripeContext('STRIPE-VIEW-001');

        $service = new class extends StripeCheckoutService {
            public function __construct()
            {
            }

            public function buildCheckoutViewData(\App\Models\Order $order, \App\Models\Pay $payGateway): array
            {
                $this->stripeCurrencyService = new class {
                    public function convertCnyToUsd(float $cny): float
                    {
                        return 1.30;
                    }
                };

                return parent::buildCheckoutViewData($order, $payGateway);
            }
        };

        $data = $service->buildCheckoutViewData($order, $payGateway);

        $this->assertSame($order->order_sn, $data['orderid']);
        $this->assertSame($payGateway->merchant_id, $data['publishable_key']);
        $this->assertSame(1000.0, $data['amount_cny']);
        $this->assertSame(130.0, $data['amount_usd']);
    }

    private function createStripeContext(string $orderSn): array
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Stripe Checkout Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Stripe Checkout Product ' . $orderSn,
            'gd_description' => 'Stripe Checkout Product Description',
            'gd_keywords' => 'stripe,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Stripe Checkout ' . $orderSn,
            'pay_check' => 'stripe-checkout-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'pk_test_stripe',
            'merchant_pem' => 'sk_test_stripe',
            'pay_handleroute' => '/pay/stripe',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Stripe Checkout Product x 1',
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
