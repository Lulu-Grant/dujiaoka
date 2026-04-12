<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellOrderControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('orders')->whereIn('id', [97001, 97002])->delete();
        DB::table('goods')->whereIn('id', [97001])->delete();
        DB::table('goods_group')->whereIn('id', [97001])->delete();
        DB::table('coupons')->whereIn('id', [97001])->delete();
        DB::table('pays')->whereIn('id', [97001])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_order_page(): void
    {
        $this->seedOrderFixture();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order');

        $response->assertOk();
        $response->assertSee('订单管理');
        $response->assertSee('XIGUA-ORDER-97001');
        $response->assertSee('测试订单 Shell');
        $response->assertSee('测试支付通道 Shell');
    }

    public function test_show_renders_order_detail_page(): void
    {
        $this->seedOrderFixture();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/97001');

        $response->assertOk();
        $response->assertSee('订单详情');
        $response->assertSee('XIGUA-ORDER-97001');
        $response->assertSee('测试商品 Shell');
        $response->assertSee('订单附加信息');
    }

    private function seedOrderFixture(): void
    {
        DB::table('goods_group')->insert([
            'id' => 97001,
            'gp_name' => '测试分类 Shell',
            'is_open' => 1,
            'ord' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('goods')->insert([
            'id' => 97001,
            'group_id' => 97001,
            'gd_name' => '测试商品 Shell',
            'gd_description' => '测试商品简介',
            'gd_keywords' => '测试关键字',
            'picture' => null,
            'retail_price' => 99,
            'actual_price' => 79,
            'in_stock' => 20,
            'sales_volume' => 5,
            'ord' => 2,
            'buy_limit_num' => 1,
            'buy_prompt' => '购买提示',
            'description' => '商品说明',
            'type' => 1,
            'wholesale_price_cnf' => null,
            'other_ipu_cnf' => null,
            'api_hook' => null,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons')->insert([
            'id' => 97001,
            'discount' => 10,
            'coupon' => '测试优惠码 Shell',
            'ret' => 1,
            'is_use' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('pays')->insert([
            'id' => 97001,
            'pay_name' => '测试支付通道 Shell',
            'merchant_id' => 'merchant-id',
            'merchant_key' => 'merchant-key',
            'merchant_pem' => 'merchant-pem',
            'pay_check' => 'stripe',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/stripe',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('orders')->insert([
            'id' => 97001,
            'order_sn' => 'XIGUA-ORDER-97001',
            'title' => '测试订单 Shell',
            'type' => 1,
            'email' => 'shell@example.com',
            'goods_id' => 97001,
            'goods_price' => 79,
            'buy_amount' => 1,
            'total_price' => 79,
            'coupon_id' => 97001,
            'coupon_discount_price' => 10,
            'wholesale_discount_price' => 0,
            'actual_price' => 69,
            'pay_id' => 97001,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => 'search-me',
            'trade_no' => 'trade-no-shell',
            'status' => 4,
            'info' => "账号: demo@example.com\n密码: 123456",
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'admin-shell-tester'],
            [
                'password' => bcrypt('secret123'),
                'name' => 'Admin Shell Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return Administrator::query()->where('username', 'admin-shell-tester')->firstOrFail();
    }
}
