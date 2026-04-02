<?php

namespace Tests\Unit;

use App\Jobs\CouponBack;
use App\Jobs\OrderExpired;
use App\Models\BaseModel;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderExpiredJobTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        config(['dujiaoka.async_side_effects' => true]);
        Queue::fake();
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_order_expired_marks_waiting_order_expired_and_dispatches_coupon_back(): void
    {
        $goods = $this->createGoods('Expire Product');
        $coupon = Coupon::query()->create([
            'discount' => 5.00,
            'is_use' => Coupon::STATUS_USE,
            'is_open' => BaseModel::STATUS_OPEN,
            'coupon' => 'EXPIRE5',
            'ret' => 0,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'EXPIRE-ORDER-1',
            'goods_id' => $goods->id,
            'coupon_id' => $coupon->id,
            'title' => 'Expire Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'coupon_discount_price' => 5.00,
            'total_price' => 10.00,
            'actual_price' => 5.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        (new OrderExpired($order->order_sn))->handle();

        $order->refresh();

        $this->assertSame(Order::STATUS_EXPIRED, $order->status);
        Queue::assertPushed(CouponBack::class);
    }

    public function test_order_expired_ignores_processed_order(): void
    {
        $goods = $this->createGoods('Processed Product');

        $order = Order::query()->create([
            'order_sn' => 'EXPIRE-ORDER-2',
            'goods_id' => $goods->id,
            'title' => 'Processed Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_PENDING,
        ]);

        (new OrderExpired($order->order_sn))->handle();

        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        Queue::assertNotPushed(CouponBack::class);
    }

    private function createGoods(string $name): Goods
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => $name . ' Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        return Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => $name,
            'gd_description' => $name . ' Description',
            'gd_keywords' => 'expire,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);
    }
}
