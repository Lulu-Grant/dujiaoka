<?php

namespace Tests\Unit;

use App\Exceptions\RuleValidationException;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Service\OrderQueryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderQueryServiceTest extends TestCase
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

    public function test_require_bill_order_rejects_expired_orders(): void
    {
        $order = $this->createOrder(Order::STATUS_EXPIRED, 'QUERY-BILL-EXPIRED');

        $service = app(OrderQueryService::class);

        $this->expectException(RuleValidationException::class);
        $this->expectExceptionMessage(__('dujiaoka.prompt.order_is_expired'));

        $service->requireBillOrder($order->order_sn);
    }

    public function test_build_status_payload_returns_success_for_processed_orders(): void
    {
        $order = $this->createOrder(Order::STATUS_PENDING, 'QUERY-STATUS-SUCCESS');

        $payload = app(OrderQueryService::class)->buildStatusPayload($order->order_sn);

        $this->assertSame(['msg' => 'success', 'code' => 200], $payload);
    }

    public function test_require_orders_by_browser_rejects_empty_cookie(): void
    {
        $service = app(OrderQueryService::class);

        $this->expectException(RuleValidationException::class);
        $this->expectExceptionMessage(__('dujiaoka.prompt.no_related_order_found_for_cache'));

        $service->requireOrdersByBrowser(null);
    }

    private function createOrder(int $status, string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Query Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Query Product ' . $orderSn,
            'gd_description' => 'Query Product Description',
            'gd_keywords' => 'query,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'title' => 'Query Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => 'secret',
            'email' => 'buyer@example.com',
            'info' => '',
            'buy_ip' => '127.0.0.1',
            'status' => $status,
        ]);
    }
}
