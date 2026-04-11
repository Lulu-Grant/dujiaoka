<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellCarmisControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('carmis')->whereIn('id', [95001, 95002])->delete();
        DB::table('goods')->whereIn('id', [95001, 95002])->delete();
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
