<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminShellCarmisControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('carmis')->whereIn('id', [95001, 95002, 95003])->delete();
        DB::table('carmis')->whereIn('carmi', ['CARD-CREATE-001', 'CARD-EDIT-002', 'CARD-NEW-001', 'CARD-NEW-002', 'CARD-UP-001', 'CARD-UP-002'])->delete();
        DB::table('goods')->whereIn('id', [95001, 95002, 95003])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_carmis_page(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/carmis');

        $response->assertOk();
        $response->assertSee('卡密管理');
        $response->assertSee('测试商品卡密 A');
        $response->assertSee('CARD-AAA-001');
    }

    public function test_show_renders_carmis_detail_page(): void
    {
        $this->seedCarmiFixture(95002, '测试商品卡密 B', 'CARD-BBB-002');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/carmis/95002');

        $response->assertOk();
        $response->assertSee('卡密详情');
        $response->assertSee('测试商品卡密 B');
        $response->assertSee('CARD-BBB-002');
    }

    public function test_import_page_renders_shell_action_form(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/carmis/import');

        $response->assertOk();
        $response->assertSee('导入卡密');
        $response->assertSee('测试商品卡密 A');
    }

    public function test_create_page_renders_carmi_action_form(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/carmis/create');

        $response->assertOk();
        $response->assertSee('新建卡密');
        $response->assertSee('测试商品卡密 A');
    }

    public function test_create_page_can_store_carmi(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/carmis/create', [
                'goods_id' => 95001,
                'status' => 1,
                'is_loop' => '1',
                'carmi' => 'CARD-CREATE-001',
            ]);

        $record = DB::table('carmis')->where('carmi', 'CARD-CREATE-001')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/carmis/'.$record->id.'/edit');
        $response->assertSessionHas('status', '卡密已创建');
        $this->assertSame(1, $record->is_loop);
    }

    public function test_edit_page_renders_carmi_action_form(): void
    {
        $this->seedCarmiFixture(95002, '测试商品卡密 B', 'CARD-BBB-002');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/carmis/95002/edit');

        $response->assertOk();
        $response->assertSee('编辑卡密');
        $response->assertSee('CARD-BBB-002');
    }

    public function test_edit_page_can_update_carmi(): void
    {
        $this->seedCarmiFixture(95002, '测试商品卡密 B', 'CARD-BBB-002');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/carmis/95002/edit', [
                'goods_id' => 95002,
                'status' => 2,
                'is_loop' => '1',
                'carmi' => 'CARD-EDIT-002',
            ]);

        $response->assertRedirect('/admin/v2/carmis/95002/edit');
        $response->assertSessionHas('status', '卡密已保存');

        $record = DB::table('carmis')->where('id', 95002)->first();
        $this->assertSame('CARD-EDIT-002', $record->carmi);
        $this->assertSame(2, $record->status);
        $this->assertSame(1, $record->is_loop);
    }

    public function test_import_page_can_import_carmis_from_text(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/carmis/import', [
                'goods_id' => 95001,
                'carmis_list' => "CARD-NEW-001\nCARD-NEW-002\nCARD-NEW-001\n",
                'remove_duplication' => '1',
            ]);

        $response->assertRedirect('/admin/v2/carmis/import');
        $response->assertSessionHas('status', '卡密导入完成，本次共导入 2 条记录');

        $this->assertSame(2, DB::table('carmis')->where('goods_id', 95001)->whereIn('carmi', ['CARD-NEW-001', 'CARD-NEW-002'])->count());
    }

    public function test_import_page_can_import_carmis_from_uploaded_file(): void
    {
        $this->seedCarmiFixture(95001, '测试商品卡密 A', 'CARD-AAA-001');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/carmis/import', [
                'goods_id' => 95001,
                'carmis_list' => '',
                'remove_duplication' => '0',
                'carmis_txt' => UploadedFile::fake()->createWithContent('upload.txt', "CARD-UP-001\nCARD-UP-002\n"),
            ]);

        $response->assertRedirect('/admin/v2/carmis/import');
        $response->assertSessionHas('status', '卡密导入完成，本次共导入 2 条记录');

        $this->assertSame(2, DB::table('carmis')->where('goods_id', 95001)->whereIn('carmi', ['CARD-UP-001', 'CARD-UP-002'])->count());
    }

    private function seedCarmiFixture(int $id, string $goodsName, string $carmi): void
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

        DB::table('carmis')->insert([
            'id' => $id,
            'goods_id' => $id,
            'status' => 1,
            'is_loop' => 0,
            'carmi' => $carmi,
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
