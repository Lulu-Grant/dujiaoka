<?php

namespace Tests\Feature;

use App\Models\GoodsGroup;
use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellGoodsGroupControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('goods_group')->whereIn('id', [91001, 91002])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_goods_group_page(): void
    {
        DB::table('goods_group')->insert([
            'id' => 91001,
            'gp_name' => 'Shell 分类',
            'is_open' => 1,
            'ord' => 9,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods-group');

        $response->assertOk();
        $response->assertSee('商品分类管理');
        $response->assertSee('Shell 分类');
    }

    public function test_show_renders_goods_group_detail_page(): void
    {
        DB::table('goods_group')->insert([
            'id' => 91002,
            'gp_name' => '详情分类',
            'is_open' => 0,
            'ord' => 3,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods-group/91002');

        $response->assertOk();
        $response->assertSee('商品分类详情');
        $response->assertSee('详情分类');
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
