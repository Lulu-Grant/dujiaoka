<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellGoodsControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('coupons_goods')->whereIn('goods_id', [96001, 96002, 96003])->delete();
        DB::table('coupons_goods')->whereIn('coupons_id', [96001, 96002, 96003])->delete();
        DB::table('coupons')->whereIn('id', [96001, 96002, 96003])->delete();
        DB::table('goods')->whereIn('id', [96001, 96002, 96003])->delete();
        DB::table('goods')->whereIn('gd_name', ['创建商品 Shell', '编辑商品 Shell'])->delete();
        DB::table('goods_group')->whereIn('id', [96001, 96002, 96003])->delete();
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
        $response->assertSee('商品壳页优先用于查找、核对和进入编辑页');
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
        $response->assertSee('编辑商品');
        $response->assertSee('基础信息');
        $response->assertSee('价格与库存');
        $response->assertSee('测试商品 Shell');
        $response->assertSee('测试优惠码 Shell');
    }

    public function test_create_page_renders_goods_action_form(): void
    {
        $this->seedActionFixtures();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods/create');

        $response->assertOk();
        $response->assertSee('新建商品');
        $response->assertSee('基础信息');
        $response->assertSee('价格与库存');
        $response->assertSee('动作分类 Shell');
        $response->assertSee('动作优惠码 Shell');
    }

    public function test_create_page_can_store_goods(): void
    {
        $this->seedActionFixtures();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/goods/create', [
                'group_id' => 96002,
                'coupon_ids' => [96002],
                'gd_name' => '创建商品 Shell',
                'gd_description' => '创建简介',
                'gd_keywords' => '创建关键词',
                'picture' => '/uploads/xigua.png',
                'type' => 2,
                'retail_price' => 199.5,
                'actual_price' => 169.9,
                'in_stock' => 12,
                'sales_volume' => 3,
                'buy_limit_num' => 2,
                'buy_prompt' => '创建购买提示',
                'description' => '创建商品说明',
                'other_ipu_cnf' => "账号\n密码",
                'wholesale_price_cnf' => "2,150\n5,140",
                'api_hook' => 'https://example.com/create-hook',
                'ord' => 7,
                'is_open' => '1',
            ]);

        $record = DB::table('goods')->where('gd_name', '创建商品 Shell')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/goods/'.$record->id.'/edit');
        $response->assertSessionHas('status', '商品已创建');
        $this->assertSame(96002, $record->group_id);
        $this->assertSame('169.90', (string) $record->actual_price);
        $this->assertSame(12, $record->in_stock);
        $this->assertSame(1, $record->is_open);
        $this->assertSame(1, DB::table('coupons_goods')->where('goods_id', $record->id)->where('coupons_id', 96002)->count());
    }

    public function test_edit_page_renders_goods_action_form(): void
    {
        $this->seedGoodsFixture();
        $this->seedActionFixtures();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods/96001/edit');

        $response->assertOk();
        $response->assertSee('编辑商品');
        $response->assertSee('说明与扩展');
        $response->assertSee('测试商品 Shell');
        $response->assertSee('动作分类 Shell');
    }

    public function test_edit_page_can_update_goods(): void
    {
        $this->seedGoodsFixture();
        $this->seedActionFixtures();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/goods/96001/edit', [
                'group_id' => 96002,
                'coupon_ids' => [96002],
                'gd_name' => '编辑商品 Shell',
                'gd_description' => '更新简介',
                'gd_keywords' => '更新关键词',
                'picture' => '/uploads/updated-xigua.png',
                'type' => 2,
                'retail_price' => 88.8,
                'actual_price' => 66.6,
                'in_stock' => 9,
                'sales_volume' => 14,
                'buy_limit_num' => 4,
                'buy_prompt' => '更新购买提示',
                'description' => '更新商品说明',
                'other_ipu_cnf' => "邮箱\n验证码",
                'wholesale_price_cnf' => "3,60\n6,55",
                'api_hook' => 'https://example.com/update-hook',
                'ord' => 11,
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/goods/96001/edit');
        $response->assertSessionHas('status', '商品已保存');

        $record = DB::table('goods')->where('id', 96001)->first();
        $this->assertSame('编辑商品 Shell', $record->gd_name);
        $this->assertSame(96002, $record->group_id);
        $this->assertSame(2, $record->type);
        $this->assertSame('66.60', (string) $record->actual_price);
        $this->assertSame(9, $record->in_stock);
        $this->assertSame(14, $record->sales_volume);
        $this->assertSame(4, $record->buy_limit_num);
        $this->assertSame(11, $record->ord);
        $this->assertSame(0, $record->is_open);
        $this->assertSame(1, DB::table('coupons_goods')->where('goods_id', 96001)->where('coupons_id', 96002)->count());
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

    private function seedActionFixtures(): void
    {
        DB::table('goods_group')->updateOrInsert(
            ['id' => 96002],
            [
                'gp_name' => '动作分类 Shell',
                'is_open' => 1,
                'ord' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );

        DB::table('coupons')->updateOrInsert(
            ['id' => 96002],
            [
                'discount' => 7,
                'coupon' => '动作优惠码 Shell',
                'ret' => 2,
                'is_use' => 1,
                'is_open' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );
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
