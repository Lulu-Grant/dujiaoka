<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellGoodsControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('coupons_goods')->where('goods_id', 96001)->delete();
        DB::table('coupons')->where('id', 96001)->delete();
        DB::table('goods')->where('id', 96001)->delete();
        DB::table('goods_group')->where('id', 96001)->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_goods_page(): void
    {
        $this->seedGoodsFixture();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods');

        $response->assertOk();
        $response->assertSee('商品管理');
        $response->assertSee('测试商品 Shell');
        $response->assertSee('测试分类 Shell');
    }

    public function test_show_renders_goods_detail_page(): void
    {
        $this->seedGoodsFixture();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods/96001');

        $response->assertOk();
        $response->assertSee('商品详情');
        $response->assertSee('测试商品 Shell');
        $response->assertSee('测试优惠码 Shell');
    }

    private function seedGoodsFixture(): void
    {
        DB::table('goods_group')->insert([
            'id' => 96001,
            'gp_name' => '测试分类 Shell',
            'is_open' => 1,
            'ord' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('goods')->insert([
            'id' => 96001,
            'group_id' => 96001,
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
            'wholesale_price_cnf' => "2,70\n5,60",
            'other_ipu_cnf' => "账号\n密码",
            'api_hook' => 'https://example.com/hook',
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons')->insert([
            'id' => 96001,
            'discount' => 8,
            'coupon' => '测试优惠码 Shell',
            'ret' => 1,
            'is_use' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons_goods')->insert([
            'coupons_id' => 96001,
            'goods_id' => 96001,
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
