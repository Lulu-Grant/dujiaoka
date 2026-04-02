<?php

namespace Tests\Unit;

use App\Exceptions\RuleValidationException;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\PayEntryService;
use App\Service\PayService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RetiredGatewayTest extends TestCase
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

    public function test_retired_gateways_are_filtered_from_pay_list(): void
    {
        Pay::query()->create([
            'pay_name' => 'Retired Payjs',
            'pay_check' => 'payjs',
            'pay_method' => Pay::METHOD_SCAN,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'retired-mch',
            'merchant_pem' => 'retired-secret',
            'pay_handleroute' => '/pay/payjs',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        Pay::query()->create([
            'pay_name' => 'Active Stripe',
            'pay_check' => 'stripe',
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'stripe-pub',
            'merchant_pem' => 'stripe-secret',
            'pay_handleroute' => '/pay/stripe',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $gateways = app(PayService::class)->pays(Pay::PAY_CLIENT_PC);

        $this->assertSame(['stripe'], array_values(array_column($gateways, 'pay_check')));
    }

    public function test_loading_retired_gateway_raises_retired_message(): void
    {
        $order = $this->createPendingOrder('RETIRED-GATEWAY-001');

        $this->expectException(RuleValidationException::class);
        $this->expectExceptionMessage(__('dujiaoka.prompt.pay_gateway_retired'));

        app(PayEntryService::class)->loadGatewayForOrder($order->order_sn, 'payjs');
    }

    private function createPendingOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Retired Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Retired Product ' . $orderSn,
            'gd_description' => 'Retired Product Description',
            'gd_keywords' => 'retired,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'title' => 'Retired Product x 1',
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
