<?php

namespace Tests\Unit;

use App\Jobs\CouponBack;
use App\Models\BaseModel;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExpireOrdersCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        config(['dujiaoka.async_side_effects' => true]);
        Queue::fake();
        Cache::put('system-setting', [
            'order_expire_time' => 5,
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_expire_orders_command_expires_only_timed_out_pending_orders(): void
    {
        $goods = $this->createGoods();
        $coupon = Coupon::query()->create([
            'discount' => 5.00,
            'is_use' => Coupon::STATUS_USE,
            'is_open' => BaseModel::STATUS_OPEN,
            'coupon' => 'LATE5',
            'ret' => 0,
        ]);

        $expiredOrder = Order::query()->create([
            'order_sn' => 'EXPIRE-CMD-OLD',
            'goods_id' => $goods->id,
            'coupon_id' => $coupon->id,
            'title' => 'Expire Command Product x 1',
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
            'created_at' => Carbon::now()->subMinutes(10),
            'updated_at' => Carbon::now()->subMinutes(10),
        ]);

        $freshOrder = Order::query()->create([
            'order_sn' => 'EXPIRE-CMD-NEW',
            'goods_id' => $goods->id,
            'title' => 'Expire Command Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
            'created_at' => Carbon::now()->subMinutes(2),
            'updated_at' => Carbon::now()->subMinutes(2),
        ]);

        $this->artisan('orders:expire', ['--minutes' => 5])
            ->expectsOutput('Expired 1 order(s).')
            ->assertExitCode(0);

        $expiredOrder->refresh();
        $freshOrder->refresh();

        $this->assertSame(Order::STATUS_EXPIRED, $expiredOrder->status);
        $this->assertSame(Order::STATUS_WAIT_PAY, $freshOrder->status);
        Queue::assertPushed(CouponBack::class, 1);
    }

    private function createGoods(): Goods
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Expire Command Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        return Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Expire Command Product',
            'gd_description' => 'Expire Command Description',
            'gd_keywords' => 'expire,command',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);
    }
}
