<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Service\OrderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        Cache::put('system-setting', [
            'template' => 'hyper',
            'is_open_search_pwd' => BaseModel::STATUS_CLOSE,
            'is_open_img_code' => BaseModel::STATUS_CLOSE,
            'is_open_geetest' => BaseModel::STATUS_CLOSE,
            'is_open_anti_red' => BaseModel::STATUS_CLOSE,
        ]);
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_with_email_and_password_returns_latest_matching_orders(): void
    {
        $goods = $this->createGoods('Lookup Product');

        Order::query()->create([
            'order_sn' => 'LOOKUP-OLD',
            'goods_id' => $goods->id,
            'title' => 'Lookup Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => 'secret',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
            'created_at' => now()->subDay(),
        ]);

        $latest = Order::query()->create([
            'order_sn' => 'LOOKUP-NEW',
            'goods_id' => $goods->id,
            'title' => 'Lookup Product x 2',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 20.00,
            'buy_amount' => 2,
            'total_price' => 20.00,
            'actual_price' => 20.00,
            'search_pwd' => 'secret',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_PENDING,
            'created_at' => now(),
        ]);

        Order::query()->create([
            'order_sn' => 'LOOKUP-OTHER',
            'goods_id' => $goods->id,
            'title' => 'Other Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => 'different',
            'email' => 'other@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
            'created_at' => now(),
        ]);

        $orders = app(OrderService::class)->withEmailAndPassword('buyer@example.com', 'secret');

        $this->assertCount(2, $orders);
        $this->assertSame($latest->order_sn, $orders->first()->order_sn);
        $this->assertSame(['LOOKUP-NEW', 'LOOKUP-OLD'], $orders->pluck('order_sn')->all());
    }

    public function test_coupon_back_job_restores_coupon_usage_count_and_marks_order(): void
    {
        $goods = $this->createGoods('Coupon Product');

        $coupon = Coupon::query()->create([
            'discount' => 5.00,
            'is_use' => Coupon::STATUS_USE,
            'is_open' => BaseModel::STATUS_OPEN,
            'coupon' => 'SAVE5',
            'ret' => 0,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'COUPON-BACK-1',
            'goods_id' => $goods->id,
            'coupon_id' => $coupon->id,
            'title' => 'Coupon Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'coupon_discount_price' => 5.00,
            'total_price' => 10.00,
            'actual_price' => 5.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_EXPIRED,
            'coupon_ret_back' => Order::COUPON_BACK_WAIT,
        ]);

        $job = new \App\Jobs\CouponBack($order);
        $job->handle();

        $coupon->refresh();
        $order->refresh();

        $this->assertSame(1, $coupon->ret);
        $this->assertSame(Order::COUPON_BACK_OK, $order->coupon_ret_back);
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
            'gd_keywords' => 'test,lookup',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);
    }
}
