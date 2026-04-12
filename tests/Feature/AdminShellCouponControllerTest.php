<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellCouponControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        $batchIds = DB::table('coupons')->where('coupon', 'like', 'XIGUA-BATCH-%')->pluck('id')->all();
        if (!empty($batchIds)) {
            DB::table('coupons_goods')->whereIn('coupons_id', $batchIds)->delete();
        }
        DB::table('coupons')->where('coupon', 'like', 'XIGUA-BATCH-%')->delete();
        DB::table('coupons_goods')->whereIn('coupons_id', [94001, 94002, 94003])->delete();
        DB::table('coupons')->whereIn('id', [94001, 94002, 94003])->delete();
        DB::table('coupons')->whereIn('coupon', ['XIGUA-5', 'XIGUA-DETAIL', 'XIGUA-CREATE', 'XIGUA-EDIT'])->delete();
        DB::table('goods')->whereIn('id', [94001, 94002, 94003])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_coupon_page(): void
    {
        $this->seedCouponFixture(94001, 'XIGUA-5', '测试商品 A');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon');

        $response->assertOk();
        $response->assertSee('优惠码管理');
        $response->assertSee('批量生成优惠码');
        $response->assertSee('复制、核对和进入编辑页');
        $response->assertSee('当前结果');
        $response->assertSee('XIGUA-5');
        $response->assertSee('测试商品 A');
    }

    public function test_show_renders_coupon_detail_page(): void
    {
        $this->seedCouponFixture(94002, 'XIGUA-DETAIL', '测试商品 B');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/94002');

        $response->assertOk();
        $response->assertSee('优惠码详情');
        $response->assertSee('复制优惠码');
        $response->assertSee('编辑优惠码');
        $response->assertSee('XIGUA-DETAIL');
        $response->assertSee('测试商品 B');
    }

    public function test_create_page_renders_coupon_action_form(): void
    {
        $this->seedCouponFixture(94001, 'XIGUA-5', '测试商品 A');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/create');

        $response->assertOk();
        $response->assertSee('新建优惠码');
        $response->assertSee('生成建议码');
        $response->assertSee('XIGUA-XXXXXX');
        $response->assertSee('测试商品 A');
    }

    public function test_batch_create_page_renders_coupon_batch_form(): void
    {
        $this->seedCouponFixture(94001, 'XIGUA-5', '测试商品 A');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/create?mode=batch');

        $response->assertOk();
        $response->assertSee('批量生成优惠码');
        $response->assertSee('批量数量');
        $response->assertSee('随机后缀长度');
        $response->assertSee('前缀 + 随机后缀');
        $response->assertSee('XIGUA-');
        $response->assertSee('测试商品 A');
    }

    public function test_create_page_can_store_coupon(): void
    {
        $this->seedGoodsOnlyFixture(94003, '测试商品 C');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/create', [
                'goods_ids' => [94003],
                'discount' => 6.5,
                'coupon' => 'XIGUA-CREATE',
                'ret' => 3,
                'is_use' => 1,
                'is_open' => '1',
            ]);

        $record = DB::table('coupons')->where('coupon', 'XIGUA-CREATE')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/coupon/'.$record->id.'/edit');
        $response->assertSessionHas('status', '优惠码已创建');
        $this->assertSame(1, DB::table('coupons_goods')->where('coupons_id', $record->id)->where('goods_id', 94003)->count());
    }

    public function test_batch_create_page_can_store_multiple_coupons(): void
    {
        $this->seedGoodsOnlyFixture(94003, '测试商品 C');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/create?mode=batch', [
                'mode' => 'batch',
                'goods_ids' => [94003],
                'quantity' => 3,
                'prefix' => 'XIGUA-BATCH-',
                'length' => 4,
                'discount' => 8.8,
                'ret' => 2,
                'is_use' => 1,
                'is_open' => '1',
            ]);

        $response->assertRedirect('/admin/v2/coupon');
        $response->assertSessionHas('status', '已批量生成 3 个优惠码');

        $records = DB::table('coupons')
            ->where('coupon', 'like', 'XIGUA-BATCH-%')
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $records);
        $this->assertSame(3, DB::table('coupons_goods')->whereIn('coupons_id', $records->pluck('id'))->where('goods_id', 94003)->count());
        $this->assertSame(3, $records->filter(function ($record) {
            return strpos($record->coupon, 'XIGUA-BATCH-') === 0;
        })->count());
    }

    public function test_edit_page_renders_coupon_action_form(): void
    {
        $this->seedCouponFixture(94002, 'XIGUA-DETAIL', '测试商品 B');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/94002/edit');

        $response->assertOk();
        $response->assertSee('编辑优惠码');
        $response->assertSee('复制优惠码');
        $response->assertSee('当前优惠码');
        $response->assertSee('XIGUA-DETAIL');
        $response->assertSee('测试商品 B');
    }

    public function test_edit_page_can_update_coupon(): void
    {
        $this->seedCouponFixture(94002, 'XIGUA-DETAIL', '测试商品 B');
        $this->seedGoodsOnlyFixture(94003, '测试商品 C');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/94002/edit', [
                'goods_ids' => [94003],
                'discount' => 9.9,
                'coupon' => 'XIGUA-EDIT',
                'ret' => 5,
                'is_use' => 2,
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/coupon/94002/edit');
        $response->assertSessionHas('status', '优惠码已保存');

        $record = DB::table('coupons')->where('id', 94002)->first();
        $this->assertSame('XIGUA-EDIT', $record->coupon);
        $this->assertSame('9.90', (string) $record->discount);
        $this->assertSame(5, $record->ret);
        $this->assertSame(2, $record->is_use);
        $this->assertSame(0, $record->is_open);
        $this->assertSame(1, DB::table('coupons_goods')->where('coupons_id', 94002)->where('goods_id', 94003)->count());
    }

    private function seedCouponFixture(int $id, string $couponCode, string $goodsName): void
    {
        $this->seedGoodsOnlyFixture($id, $goodsName);

        DB::table('coupons')->insert([
            'id' => $id,
            'discount' => 5,
            'coupon' => $couponCode,
            'ret' => 1,
            'is_use' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons_goods')->insert([
            'coupons_id' => $id,
            'goods_id' => $id,
        ]);
    }

    private function seedGoodsOnlyFixture(int $id, string $goodsName): void
    {
        DB::table('goods')->updateOrInsert(
            ['id' => $id],
            [
                'group_id' => 1,
                'gd_name' => $goodsName,
                'gd_description' => 'desc',
                'gd_keywords' => 'key',
                'picture' => null,
                'retail_price' => 10,
                'actual_price' => 10,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 1,
                'buy_limit_num' => 0,
                'buy_prompt' => null,
                'description' => 'inst',
                'type' => 1,
                'wholesale_price_cnf' => null,
                'other_ipu_cnf' => null,
                'api_hook' => null,
                'is_open' => 1,
                'created_at' => now(),
                'updated_at' => now(),
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
