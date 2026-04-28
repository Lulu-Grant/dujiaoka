<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Service\OrderActionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderActionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('orders')->where('id', 98001)->delete();

        parent::tearDown();
    }

    public function test_reset_search_password_generates_new_password_and_persists_it(): void
    {
        $order = $this->seedOrderFixture(98001);

        /** @var \App\Service\OrderActionService $service */
        $service = $this->app->make(OrderActionService::class);
        $newPassword = $service->resetSearchPassword($order);

        $order->refresh();

        $this->assertNotSame('search-me', $newPassword);
        $this->assertStringStartsWith('XG-', $newPassword);
        $this->assertSame($newPassword, $order->search_pwd);
    }

    public function test_batch_title_defaults_start_empty_for_safe_review(): void
    {
        $defaults = app(OrderActionService::class)->batchTitleDefaults([98001, 98002]);

        $this->assertSame([98001, 98002], $defaults['order_ids']);
        $this->assertSame("98001\n98002", $defaults['ids_text']);
        $this->assertSame('', $defaults['title']);
    }

    public function test_batch_title_prefix_defaults_start_empty_for_safe_review(): void
    {
        $defaults = app(OrderActionService::class)->batchTitlePrefixDefaults([98001, 98002]);

        $this->assertSame([98001, 98002], $defaults['order_ids']);
        $this->assertSame("98001\n98002", $defaults['ids_text']);
        $this->assertSame('', $defaults['title_prefix']);
    }

    private function seedOrderFixture(int $id): Order
    {
        DB::table('orders')->where('id', $id)->delete();
        DB::table('orders')->insert([
            'id' => $id,
            'order_sn' => 'XIGUA-ORDER-'.$id,
            'title' => '测试订单 Shell '.$id,
            'type' => 1,
            'email' => 'shell@example.com',
            'goods_id' => 0,
            'goods_price' => 79,
            'buy_amount' => 1,
            'total_price' => 79,
            'coupon_id' => 0,
            'coupon_discount_price' => 10,
            'wholesale_discount_price' => 0,
            'actual_price' => 69,
            'pay_id' => 0,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => 'search-me',
            'trade_no' => 'trade-no-shell',
            'status' => 4,
            'info' => "账号: demo@example.com {$id}\n密码: 123456 {$id}",
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        return Order::query()->findOrFail($id);
    }
}
