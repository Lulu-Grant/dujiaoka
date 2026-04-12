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
        DB::table('goods_group')->whereIn('id', [91001, 91002, 91003])->delete();
        DB::table('goods_group')->whereIn('gp_name', ['新分类', '已更新分类'])->delete();
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

    public function test_create_page_renders_goods_group_action_form(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods-group/create');

        $response->assertOk();
        $response->assertSee('新建商品分类');
        $response->assertSee('分类名称');
    }

    public function test_create_page_can_store_goods_group(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/goods-group/create', [
                'gp_name' => '新分类',
                'ord' => 12,
                'is_open' => '1',
            ]);

        $record = DB::table('goods_group')->where('gp_name', '新分类')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/goods-group/'.$record->id.'/edit');
        $response->assertSessionHas('status', '商品分类已创建');
        $this->assertSame(12, $record->ord);
        $this->assertSame(1, $record->is_open);
    }

    public function test_edit_page_renders_goods_group_action_form(): void
    {
        DB::table('goods_group')->insert([
            'id' => 91003,
            'gp_name' => '可编辑分类',
            'is_open' => 1,
            'ord' => 8,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/goods-group/91003/edit');

        $response->assertOk();
        $response->assertSee('编辑商品分类');
        $response->assertSee('可编辑分类');
    }

    public function test_edit_page_can_update_goods_group(): void
    {
        DB::table('goods_group')->insert([
            'id' => 91003,
            'gp_name' => '可编辑分类',
            'is_open' => 1,
            'ord' => 8,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/goods-group/91003/edit', [
                'gp_name' => '已更新分类',
                'ord' => 15,
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/goods-group/91003/edit');
        $response->assertSessionHas('status', '商品分类已保存');

        $record = DB::table('goods_group')->where('id', 91003)->first();
        $this->assertSame('已更新分类', $record->gp_name);
        $this->assertSame(15, $record->ord);
        $this->assertSame(0, $record->is_open);
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
