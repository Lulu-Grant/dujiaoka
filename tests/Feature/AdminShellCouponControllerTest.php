<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellCouponControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('coupons_goods')->whereIn('coupons_id', [94001, 94002])->delete();
        DB::table('coupons')->whereIn('id', [94001, 94002])->delete();
        DB::table('goods')->whereIn('id', [94001, 94002])->delete();
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
        $response->assertSee('XIGUA-DETAIL');
        $response->assertSee('测试商品 B');
    }

    private function seedCouponFixture(int $id, string $couponCode, string $goodsName): void
    {
        DB::table('goods')->insert([
            'id' => $id,
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
        ]);

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
