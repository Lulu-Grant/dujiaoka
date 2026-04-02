<?php

namespace Tests\Unit;

use App\Http\Controllers\Home\OrderController;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrderStatusControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        Cache::put('system-setting', [
            'template' => 'hyper',
            'is_open_anti_red' => BaseModel::STATUS_CLOSE,
            'is_open_geetest' => BaseModel::STATUS_CLOSE,
            'language' => 'zh_CN',
        ]);
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_check_order_status_returns_wait_for_unpaid_order(): void
    {
        $order = $this->createOrder(Order::STATUS_WAIT_PAY, 'STATUS-WAIT');

        $response = app(OrderController::class)->checkOrderStatus($order->order_sn);

        $this->assertSame([
            'msg' => 'wait....',
            'code' => 400000,
        ], $response->getData(true));
    }

    public function test_check_order_status_returns_success_for_processed_order(): void
    {
        $order = $this->createOrder(Order::STATUS_PENDING, 'STATUS-SUCCESS');

        $response = app(OrderController::class)->checkOrderStatus($order->order_sn);

        $this->assertSame([
            'msg' => 'success',
            'code' => 200,
        ], $response->getData(true));
    }

    private function createOrder(int $status, string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Status Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Status Product ' . $orderSn,
            'gd_description' => 'Status Product Description',
            'gd_keywords' => 'status,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'title' => 'Status Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => $status,
        ]);
    }
}
