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
        DB::table('coupons')->whereIn('id', [95001, 95002, 95003, 95004, 95005, 95006, 95007, 95008])->delete();
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
        $response->assertSee('导出优惠码文本');
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
            ->post('/admin/v2/coupon/create', [
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

    public function test_batch_status_page_renders_coupon_status_form_and_preview(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-STATE-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-STATE-2', '测试商品 E');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-status?ids=95001,95002');

        $response->assertOk();
        $response->assertSee('批量启停优惠码');
        $response->assertSee('优惠码 ID 列表');
        $response->assertSee('匹配预览');
        $response->assertSee('XIGUA-STATE-1');
        $response->assertSee('XIGUA-STATE-2');
        $response->assertSee('已启用');
    }

    public function test_batch_status_page_can_update_coupon_open_status_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-STATE-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-STATE-2', '测试商品 E');
        $this->seedCouponFixture(95003, 'XIGUA-STATE-3', '测试商品 F');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-status', [
                'ids_text' => "95001, 95002\n95003",
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-status?ids=95001,95002,95003');
        $response->assertSessionHas('status', '已批量停用 3 个优惠码');

        $this->assertSame(0, (int) DB::table('coupons')->where('id', 95001)->value('is_open'));
        $this->assertSame(0, (int) DB::table('coupons')->where('id', 95002)->value('is_open'));
        $this->assertSame(0, (int) DB::table('coupons')->where('id', 95003)->value('is_open'));
    }

    public function test_batch_ret_page_renders_coupon_ret_form_and_preview(): void
    {
        $this->seedCouponFixture(95004, 'XIGUA-RET-1', '测试商品 G');
        $this->seedCouponFixture(95005, 'XIGUA-RET-2', '测试商品 H');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-ret?ids=95004,95005');

        $response->assertOk();
        $response->assertSee('批量设置优惠码可用次数');
        $response->assertSee('目标可用次数');
        $response->assertSee('匹配预览');
        $response->assertSee('XIGUA-RET-1');
        $response->assertSee('XIGUA-RET-2');
        $response->assertSee('当前可用次数');
    }

    public function test_batch_use_page_renders_coupon_use_form_and_preview(): void
    {
        $this->seedCouponFixture(95004, 'XIGUA-USE-1', '测试商品 G');
        $this->seedCouponFixture(95005, 'XIGUA-USE-2', '测试商品 H');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-use?ids=95004,95005');

        $response->assertOk();
        $response->assertSee('批量设置优惠码使用状态');
        $response->assertSee('目标使用状态');
        $response->assertSee('匹配预览');
        $response->assertSee('XIGUA-USE-1');
        $response->assertSee('XIGUA-USE-2');
        $response->assertSee('当前使用状态');
    }

    public function test_batch_ret_page_can_update_coupon_ret_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-RET-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-RET-2', '测试商品 E');
        $this->seedCouponFixture(95003, 'XIGUA-RET-3', '测试商品 F');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-ret', [
                'ids_text' => "95001, 95002\n95003",
                'ret' => 6,
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-ret?ids=95001,95002,95003');
        $response->assertSessionHas('status', '已批量把 3 个优惠码的可用次数调整为 6 次');

        $this->assertSame(6, (int) DB::table('coupons')->where('id', 95001)->value('ret'));
        $this->assertSame(6, (int) DB::table('coupons')->where('id', 95002)->value('ret'));
        $this->assertSame(6, (int) DB::table('coupons')->where('id', 95003)->value('ret'));
    }

    public function test_batch_use_page_can_update_coupon_usage_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-USE-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-USE-2', '测试商品 E');
        $this->seedCouponFixture(95003, 'XIGUA-USE-3', '测试商品 F');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-use', [
                'ids_text' => "95001, 95002\n95003",
                'is_use' => (string) \App\Models\Coupon::STATUS_USE,
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-use?ids=95001,95002,95003');
        $response->assertSessionHas('status', '已批量把 3 个优惠码的使用状态调整为 '.admin_trans('coupon.fields.status_use'));

        $this->assertSame(\App\Models\Coupon::STATUS_USE, (int) DB::table('coupons')->where('id', 95001)->value('is_use'));
        $this->assertSame(\App\Models\Coupon::STATUS_USE, (int) DB::table('coupons')->where('id', 95002)->value('is_use'));
        $this->assertSame(\App\Models\Coupon::STATUS_USE, (int) DB::table('coupons')->where('id', 95003)->value('is_use'));
    }

    public function test_batch_discount_page_renders_coupon_discount_form_and_preview(): void
    {
        $this->seedCouponFixture(95004, 'XIGUA-DISCOUNT-1', '测试商品 G');
        $this->seedCouponFixture(95005, 'XIGUA-DISCOUNT-2', '测试商品 H');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-discount?ids=95004,95005');

        $response->assertOk();
        $response->assertSee('批量设置优惠码折扣');
        $response->assertSee('目标折扣金额');
        $response->assertSee('匹配预览');
        $response->assertSee('XIGUA-DISCOUNT-1');
        $response->assertSee('XIGUA-DISCOUNT-2');
        $response->assertSee('当前折扣');
    }

    public function test_batch_discount_page_can_update_coupon_discount_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-DISCOUNT-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-DISCOUNT-2', '测试商品 E');
        $this->seedCouponFixture(95003, 'XIGUA-DISCOUNT-3', '测试商品 F');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-discount', [
                'ids_text' => "95001, 95002\n95003",
                'discount' => '8.8',
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-discount?ids=95001,95002,95003');
        $response->assertSessionHas('status', '已批量把 3 个优惠码的折扣调整为 8.8');

        $this->assertSame('8.80', (string) DB::table('coupons')->where('id', 95001)->value('discount'));
        $this->assertSame('8.80', (string) DB::table('coupons')->where('id', 95002)->value('discount'));
        $this->assertSame('8.80', (string) DB::table('coupons')->where('id', 95003)->value('discount'));
    }

    public function test_batch_code_page_renders_coupon_code_form_and_preview(): void
    {
        DB::table('coupons_goods')->whereIn('coupons_id', [95006, 95007])->delete();
        DB::table('coupons')->whereIn('id', [95006, 95007])->delete();

        $this->seedCouponFixture(95006, 'XIGUA-CODE-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-CODE-2', '测试商品 J');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-code?ids=95006,95007');

        $response->assertOk();
        $response->assertSee('批量重生成优惠码内容');
        $response->assertSee('目标前缀');
        $response->assertSee('随机段长度');
        $response->assertSee('XIGUA-CODE-1');
        $response->assertSee('XIGUA-CODE-2');
    }

    public function test_batch_code_page_can_regenerate_coupon_codes_with_mixed_separators(): void
    {
        DB::table('coupons_goods')->whereIn('coupons_id', [95006, 95007])->delete();
        DB::table('coupons')->whereIn('id', [95006, 95007])->delete();

        $this->seedCouponFixture(95006, 'XIGUA-CODE-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-CODE-2', '测试商品 J');
        $beforeDiscount = (string) DB::table('coupons')->where('id', 95006)->value('discount');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-code', [
                'ids_text' => "95006, 95007\n95008",
                'prefix' => 'SPRING-',
                'length' => 5,
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-code?ids=95006,95007,95008');
        $response->assertSessionHas('status', '已批量重生成 2 个优惠码内容');

        $firstCode = (string) DB::table('coupons')->where('id', 95006)->value('coupon');
        $secondCode = (string) DB::table('coupons')->where('id', 95007)->value('coupon');

        $this->assertStringStartsWith('SPRING-', $firstCode);
        $this->assertStringStartsWith('SPRING-', $secondCode);
        $this->assertNotSame('XIGUA-CODE-1', $firstCode);
        $this->assertNotSame('XIGUA-CODE-2', $secondCode);
        $this->assertNotSame($firstCode, $secondCode);
        $this->assertSame($beforeDiscount, (string) DB::table('coupons')->where('id', 95006)->value('discount'));
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95006)->value('ret'));
        $this->assertSame(\App\Models\Coupon::STATUS_OPEN, (int) DB::table('coupons')->where('id', 95006)->value('is_open'));
    }

    public function test_batch_code_prefix_page_renders_coupon_prefix_form_and_preview(): void
    {
        $this->seedCouponFixture(95006, 'XIGUA-PREFIX-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-PREFIX-2', '测试商品 J');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-code-prefix?ids=95006,95007');

        $response->assertOk();
        $response->assertSee('批量添加优惠码前缀');
        $response->assertSee('目标前缀');
        $response->assertSee('XIGUA-PREFIX-1');
        $response->assertSee('XIGUA-PREFIX-2');
    }

    public function test_batch_code_prefix_page_can_add_coupon_prefix_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95006, 'XIGUA-PREFIX-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-PREFIX-2', '测试商品 J');
        $beforeDiscount = (string) DB::table('coupons')->where('id', 95006)->value('discount');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-code-prefix', [
                'ids_text' => "95006, 95007\n95008",
                'prefix' => 'SPRING-',
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-code-prefix?ids=95006,95007,95008');
        $response->assertSessionHas('status', '已批量为 2 个优惠码添加前缀');

        $this->assertSame('SPRING-XIGUA-PREFIX-1', (string) DB::table('coupons')->where('id', 95006)->value('coupon'));
        $this->assertSame('SPRING-XIGUA-PREFIX-2', (string) DB::table('coupons')->where('id', 95007)->value('coupon'));
        $this->assertSame($beforeDiscount, (string) DB::table('coupons')->where('id', 95006)->value('discount'));
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95006)->value('ret'));
        $this->assertSame(\App\Models\Coupon::STATUS_OPEN, (int) DB::table('coupons')->where('id', 95006)->value('is_open'));
    }

    public function test_batch_code_suffix_page_renders_coupon_suffix_form_and_preview(): void
    {
        $this->seedCouponFixture(95006, 'XIGUA-SUFFIX-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-SUFFIX-2', '测试商品 J');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon/batch-code-suffix?ids=95006,95007');

        $response->assertOk();
        $response->assertSee('批量添加优惠码后缀');
        $response->assertSee('目标后缀');
        $response->assertSee('XIGUA-SUFFIX-1');
        $response->assertSee('XIGUA-SUFFIX-2');
    }

    public function test_batch_code_suffix_page_can_add_coupon_suffix_with_mixed_separators(): void
    {
        $this->seedCouponFixture(95006, 'XIGUA-SUFFIX-1', '测试商品 I');
        $this->seedCouponFixture(95007, 'XIGUA-SUFFIX-2', '测试商品 J');
        $beforeDiscount = (string) DB::table('coupons')->where('id', 95006)->value('discount');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/coupon/batch-code-suffix', [
                'ids_text' => "95006, 95007\n95008",
                'suffix' => '-SPRING',
            ]);

        $response->assertRedirect('/admin/v2/coupon/batch-code-suffix?ids=95006,95007,95008');
        $response->assertSessionHas('status', '已批量为 2 个优惠码添加后缀');

        $this->assertSame('XIGUA-SUFFIX-1-SPRING', (string) DB::table('coupons')->where('id', 95006)->value('coupon'));
        $this->assertSame('XIGUA-SUFFIX-2-SPRING', (string) DB::table('coupons')->where('id', 95007)->value('coupon'));
        $this->assertSame($beforeDiscount, (string) DB::table('coupons')->where('id', 95006)->value('discount'));
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95006)->value('ret'));
        $this->assertSame(\App\Models\Coupon::STATUS_OPEN, (int) DB::table('coupons')->where('id', 95006)->value('is_open'));
    }

    public function test_index_can_export_coupon_text_with_current_filters(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-STATE-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-STATE-2', '测试商品 E');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon?goods_id=95001&export=text');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="coupon-export-', $response->headers->get('Content-Disposition'));
        $response->assertSee('独角数卡西瓜版 - 优惠码文本导出');
        $response->assertSee('筛选条件：商品ID=95001');
        $response->assertSee('优惠码：XIGUA-STATE-1');
        $response->assertSee('折扣：5');
        $response->assertSee('启用状态：已启用');
        $response->assertSee('使用状态：未使用');
        $response->assertSee('关联商品：测试商品 D');
        $response->assertDontSee('XIGUA-STATE-2');
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95001)->value('is_open'));
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95002)->value('is_open'));
    }

    public function test_index_can_export_coupon_csv_with_current_filters(): void
    {
        $this->seedCouponFixture(95001, 'XIGUA-STATE-1', '测试商品 D');
        $this->seedCouponFixture(95002, 'XIGUA-STATE-2', '测试商品 E');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/coupon?goods_id=95001&export=csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="coupon-export-', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('优惠码,ID,折扣金额,使用状态,启用状态,可用次数,关联商品,删除状态,更新时间', $response->getContent());
        $this->assertStringContainsString('XIGUA-STATE-1,95001,5.00,未使用,已启用,1,"测试商品 D",正常', $response->getContent());
        $response->assertDontSee('XIGUA-STATE-2');
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95001)->value('is_open'));
        $this->assertSame(1, (int) DB::table('coupons')->where('id', 95002)->value('is_open'));
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
