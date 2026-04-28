<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminShell\PayActionController;
use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminShellPayControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('pays')->whereIn('id', [93001, 93002, 93003, 93004, 93005, 93006, 93011, 93012, 93013, 93014, 93015, 93016, 93017, 93018, 93019, 93020, 93021, 93022, 93023, 93024, 93025, 93026, 93027, 93028, 93029, 93030, 93031, 93032, 93033, 93034, 93035, 93036, 93037, 93038, 93039, 93040, 93041, 93042, 93043])->delete();
        DB::table('pays')->whereIn('pay_check', ['stripe', 'paypal', 'wechat-shell', 'alipay-shell', 'copy-shell-clone'])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_pay_page(): void
    {
        DB::table('pays')->insert([
            'id' => 93001,
            'pay_name' => 'Stripe 样板',
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

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay');

        $response->assertOk();
        $response->assertSee('支付通道管理');
        $response->assertSee('Stripe 样板');
        $response->assertSee('导出结构化 CSV');
        $response->assertSee('导出当前筛选');
    }

    public function test_index_can_export_filtered_pay_channels_as_desensitized_text(): void
    {
        DB::table('pays')->insert([
            'id' => 93030,
            'pay_name' => '导出样板 A',
            'merchant_id' => 'export-merchant-a',
            'merchant_key' => 'export-secret-key-a',
            'merchant_pem' => 'export-secret-pem-a',
            'pay_check' => 'export-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/export-a',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93031,
            'pay_name' => '导出样板 B',
            'merchant_id' => 'export-merchant-b',
            'merchant_key' => 'export-secret-key-b',
            'merchant_pem' => 'export-secret-pem-b',
            'pay_check' => 'export-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/export-b',
            'pay_method' => 1,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay?pay_name=%E5%AF%BC%E5%87%BA%E6%A0%B7%E6%9D%BF&export=1');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $response->assertSee('支付通道导出');
        $response->assertSee('筛选条件：');
        $response->assertSee('导出样板 A');
        $response->assertSee('导出样板 B');
        $response->assertSee('商户 KEY：已脱敏');
        $response->assertSee('商户 PEM：已脱敏');
        $response->assertDontSee('export-secret-key-a');
        $response->assertDontSee('export-secret-pem-a');
        $response->assertDontSee('export-secret-key-b');
        $response->assertDontSee('export-secret-pem-b');
    }

    public function test_index_can_export_filtered_pay_channels_as_csv(): void
    {
        DB::table('pays')->insert([
            'id' => 93020,
            'pay_name' => 'CSV 样板 A',
            'merchant_id' => 'csv-merchant-a',
            'merchant_key' => 'csv-secret-key-a',
            'merchant_pem' => 'csv-secret-pem-a',
            'pay_check' => 'csv-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/csv-a',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93021,
            'pay_name' => 'CSV 样板 B',
            'merchant_id' => 'csv-merchant-b',
            'merchant_key' => 'csv-secret-key-b',
            'merchant_pem' => 'csv-secret-pem-b',
            'pay_check' => 'csv-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/csv-b',
            'pay_method' => 1,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay?pay_name=CSV&export=csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $response->assertSee('ID,支付名称,支付标识,生命周期,支付场景,支付方式,启用状态,支付路由,"商户 ID","商户 KEY","商户 PEM",更新时间');
        $response->assertSee('93020,"CSV 样板 A",csv-a');
        $response->assertSee('93021,"CSV 样板 B",csv-b');
        $response->assertSee('已脱敏');
        $response->assertDontSee('csv-secret-key-a');
        $response->assertDontSee('csv-secret-pem-a');
        $response->assertDontSee('csv-secret-key-b');
        $response->assertDontSee('csv-secret-pem-b');
    }

    public function test_show_renders_pay_detail_page(): void
    {
        DB::table('pays')->insert([
            'id' => 93002,
            'pay_name' => '展示样板',
            'merchant_id' => 'show-id',
            'merchant_key' => 'show-key',
            'merchant_pem' => 'show-pem',
            'pay_check' => 'show-shell',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/show-shell',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93002');

        $response->assertOk();
        $response->assertSee('支付通道详情');
        $response->assertSee('展示样板');
        $response->assertSee('/pay/show-shell');
    }

    public function test_batch_status_page_renders_pay_preview(): void
    {
        $this->registerBatchStatusRoutes();

        DB::table('pays')->insert([
            'id' => 93011,
            'pay_name' => '批量启用样板 A',
            'merchant_id' => 'batch-a',
            'merchant_key' => 'batch-a-key',
            'merchant_pem' => 'batch-a-pem',
            'pay_check' => 'batch-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-a',
            'pay_method' => 1,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93012,
            'pay_name' => '批量启用样板 B',
            'merchant_id' => 'batch-b',
            'merchant_key' => 'batch-b-key',
            'merchant_pem' => 'batch-b-pem',
            'pay_check' => 'batch-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-b',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/actions/batch-status?ids=93011,93012,93015');

        $response->assertOk();
        $response->assertSee('批量启停支付通道');
        $response->assertSee('待处理通道数');
        $response->assertSee('批量启用样板 A');
        $response->assertSee('批量启用样板 B');
        $response->assertSee('93015');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_create_page_renders_pay_action_form(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/create');

        $response->assertOk();
        $response->assertSee('新建支付通道');
        $response->assertSee('支付配置分组');
        $response->assertSee('商户与密钥');
    }

    public function test_create_page_can_copy_existing_pay_channel(): void
    {
        DB::table('pays')->insert([
            'id' => 93005,
            'pay_name' => '复制样板',
            'merchant_id' => 'copy-merchant',
            'merchant_key' => 'copy-key',
            'merchant_pem' => 'copy-pem',
            'pay_check' => 'copy-shell',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/copy-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/create?copy=93005');

        $response->assertOk();
        $response->assertSee('复制支付通道');
        $response->assertSee('正在复制支付通道');
        $response->assertSee('复制样板（副本）');
        $response->assertSee('copy-merchant');
        $response->assertSee('/pay/copy-shell');
        $response->assertDontSee('copy-key');
        $response->assertDontSee('copy-pem');
    }

    public function test_copied_pay_channel_can_be_saved_as_new_record(): void
    {
        DB::table('pays')->insert([
            'id' => 93005,
            'pay_name' => '复制样板',
            'merchant_id' => 'copy-merchant',
            'merchant_key' => 'copy-key',
            'merchant_pem' => 'copy-pem',
            'pay_check' => 'copy-shell',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/copy-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/create?copy=93005', [
                'pay_name' => '复制样板（副本）',
                'merchant_id' => 'copy-merchant',
                'merchant_key' => 'new-copy-key',
                'merchant_pem' => 'new-copy-pem',
                'pay_check' => 'copy-shell-clone',
                'pay_client' => 2,
                'pay_method' => 1,
                'pay_handleroute' => '/pay/copy-shell-clone',
                'is_open' => '1',
            ]);

        $record = DB::table('pays')->where('pay_check', 'copy-shell-clone')->first();
        $this->assertNotNull($record);
        $response->assertStatus(302);
        $response->assertSessionHas('status', '支付通道已创建');
        $this->assertSame('复制样板（副本）', $record->pay_name);
        $this->assertSame('copy-merchant', $record->merchant_id);
        $this->assertSame('new-copy-key', $record->merchant_key);
        $this->assertSame('new-copy-pem', $record->merchant_pem);
        $this->assertSame('/pay/copy-shell-clone', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(1, $record->pay_method);
        $this->assertSame(1, $record->is_open);
    }

    public function test_create_page_can_store_pay_channel(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/create', [
                'pay_name' => '微信样板',
                'merchant_id' => 'wechat-merchant',
                'merchant_key' => 'merchant-key',
                'merchant_pem' => 'merchant-pem',
                'pay_check' => 'wechat-shell',
                'pay_client' => 1,
                'pay_method' => 2,
                'pay_handleroute' => '/pay/wechat-shell',
                'is_open' => '1',
            ]);

        $record = DB::table('pays')->where('pay_check', 'wechat-shell')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/pay/'.$record->id.'/edit');
        $response->assertSessionHas('status', '支付通道已创建');
    }

    public function test_edit_page_renders_pay_action_form(): void
    {
        DB::table('pays')->insert([
            'id' => 93003,
            'pay_name' => '支付宝样板',
            'merchant_id' => 'ali-id',
            'merchant_key' => 'ali-key',
            'merchant_pem' => 'ali-pem',
            'pay_check' => 'alipay-shell',
            'pay_client' => 3,
            'pay_handleroute' => '/pay/alipay-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93003/edit');

        $response->assertOk();
        $response->assertSee('编辑支付通道');
        $response->assertSee('留空保持现有值');
        $response->assertSee('商户与密钥');
        $response->assertDontSee('ali-key');
        $response->assertDontSee('ali-pem');
    }

    public function test_index_and_show_pages_include_copy_action(): void
    {
        DB::table('pays')->insert([
            'id' => 93006,
            'pay_name' => '复制入口样板',
            'merchant_id' => 'copy-entry-id',
            'merchant_key' => 'copy-entry-key',
            'merchant_pem' => 'copy-entry-pem',
            'pay_check' => 'copy-entry',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/copy-entry',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay');
        $response->assertOk();
        $response->assertSee('复制通道');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93006');
        $response->assertOk();
        $response->assertSee('复制通道');
    }

    public function test_batch_status_page_can_update_pay_channels(): void
    {
        $this->registerBatchStatusRoutes();

        DB::table('pays')->insert([
            'id' => 93013,
            'pay_name' => '批量停用样板 A',
            'merchant_id' => 'batch-c',
            'merchant_key' => 'batch-c-key',
            'merchant_pem' => 'batch-c-pem',
            'pay_check' => 'batch-c',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-c',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93014,
            'pay_name' => '批量停用样板 B',
            'merchant_id' => 'batch-d',
            'merchant_key' => 'batch-d-key',
            'merchant_pem' => 'batch-d-pem',
            'pay_check' => 'batch-d',
            'pay_client' => 3,
            'pay_handleroute' => '/pay/batch-d',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/actions/batch-status', [
                'ids_text' => "93013\n93014",
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-status?ids=93013,93014');
        $response->assertSessionHas('status', '已批量停用 2 个支付通道');

        $this->assertSame(0, (int) DB::table('pays')->where('id', 93013)->value('is_open'));
        $this->assertSame(0, (int) DB::table('pays')->where('id', 93014)->value('is_open'));
    }

    public function test_batch_status_page_reports_missing_ids(): void
    {
        $this->registerBatchStatusRoutes();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/actions/batch-status?ids=93015');

        $response->assertOk();
        $response->assertSee('未匹配 ID 数');
        $response->assertSee('以下 ID 没有找到对应支付通道：93015');
    }

    public function test_batch_client_page_renders_pay_preview(): void
    {
        DB::table('pays')->insert([
            'id' => 93016,
            'pay_name' => '批量场景样板 A',
            'merchant_id' => 'batch-client-a',
            'merchant_key' => 'batch-client-a-key',
            'merchant_pem' => 'batch-client-a-pem',
            'pay_check' => 'batch-client-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-client-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93017,
            'pay_name' => '批量场景样板 B',
            'merchant_id' => 'batch-client-b',
            'merchant_key' => 'batch-client-b-key',
            'merchant_pem' => 'batch-client-b-pem',
            'pay_check' => 'batch-client-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-client-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-client?ids=93016,93017,93018');

        $response->assertOk();
        $response->assertSee('批量切换支付场景');
        $response->assertSee('待处理通道数');
        $response->assertSee('批量场景样板 A');
        $response->assertSee('批量场景样板 B');
        $response->assertSee('93018');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_client_page_can_update_pay_channels(): void
    {
        DB::table('pays')->insert([
            'id' => 93016,
            'pay_name' => '批量场景样板 A',
            'merchant_id' => 'batch-client-a',
            'merchant_key' => 'batch-client-a-key',
            'merchant_pem' => 'batch-client-a-pem',
            'pay_check' => 'batch-client-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-client-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93017,
            'pay_name' => '批量场景样板 B',
            'merchant_id' => 'batch-client-b',
            'merchant_key' => 'batch-client-b-key',
            'merchant_pem' => 'batch-client-b-pem',
            'pay_check' => 'batch-client-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-client-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-client', [
                'ids_text' => "93016\n93017\n93018",
                'pay_client' => '3',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-client?ids=93016,93017,93018');
        $response->assertSessionHas('status', '已批量切换 2 个支付通道到 '.admin_trans('pay.fields.pay_client_all').' 场景');

        $this->assertSame(3, (int) DB::table('pays')->where('id', 93016)->value('pay_client'));
        $this->assertSame(3, (int) DB::table('pays')->where('id', 93017)->value('pay_client'));
    }

    public function test_batch_method_page_renders_pay_preview(): void
    {
        DB::table('pays')->insert([
            'id' => 93018,
            'pay_name' => '批量方式样板 A',
            'merchant_id' => 'batch-method-a',
            'merchant_key' => 'batch-method-a-key',
            'merchant_pem' => 'batch-method-a-pem',
            'pay_check' => 'batch-method-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-method-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93019,
            'pay_name' => '批量方式样板 B',
            'merchant_id' => 'batch-method-b',
            'merchant_key' => 'batch-method-b-key',
            'merchant_pem' => 'batch-method-b-pem',
            'pay_check' => 'batch-method-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-method-b',
            'pay_method' => 1,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-method?ids=93018,93019,93022');

        $response->assertOk();
        $response->assertSee('批量切换支付方式');
        $response->assertSee('待处理通道数');
        $response->assertSee('批量方式样板 A');
        $response->assertSee('批量方式样板 B');
        $response->assertSee('93022');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_method_page_can_update_pay_channels(): void
    {
        DB::table('pays')->insert([
            'id' => 93018,
            'pay_name' => '批量方式样板 A',
            'merchant_id' => 'batch-method-a',
            'merchant_key' => 'batch-method-a-key',
            'merchant_pem' => 'batch-method-a-pem',
            'pay_check' => 'batch-method-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-method-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93019,
            'pay_name' => '批量方式样板 B',
            'merchant_id' => 'batch-method-b',
            'merchant_key' => 'batch-method-b-key',
            'merchant_pem' => 'batch-method-b-pem',
            'pay_check' => 'batch-method-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-method-b',
            'pay_method' => 1,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-method', [
                'ids_text' => "93018\n93019\n93022",
                'pay_method' => '2',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-method?ids=93018,93019,93022');
        $response->assertSessionHas('status', '已批量切换 2 个支付通道到 '.admin_trans('pay.fields.method_scan').' 方式');

        $this->assertSame(2, (int) DB::table('pays')->where('id', 93018)->value('pay_method'));
        $this->assertSame(2, (int) DB::table('pays')->where('id', 93019)->value('pay_method'));
    }

    public function test_batch_name_page_renders_pay_preview(): void
    {
        DB::table('pays')
            ->whereIn('id', [93023, 93024])
            ->orWhereIn('pay_check', ['batch-name-a', 'batch-name-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93023,
            'pay_name' => '批量名称样板 A',
            'merchant_id' => 'batch-name-a',
            'merchant_key' => 'batch-name-a-key',
            'merchant_pem' => 'batch-name-a-pem',
            'pay_check' => 'batch-name-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-name-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93024,
            'pay_name' => '批量名称样板 B',
            'merchant_id' => 'batch-name-b',
            'merchant_key' => 'batch-name-b-key',
            'merchant_pem' => 'batch-name-b-pem',
            'pay_check' => 'batch-name-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-name-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-name?ids=93023,93024,93025');

        $response->assertOk();
        $response->assertSee('批量设置支付名称');
        $response->assertSee('待处理通道数');
        $response->assertSee('批量名称样板 A');
        $response->assertSee('批量名称样板 B');
        $response->assertSee('93025');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_name_page_can_update_pay_channels(): void
    {
        DB::table('pays')
            ->whereIn('id', [93026, 93027])
            ->orWhereIn('pay_check', ['batch-name-update-a', 'batch-name-update-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93026,
            'pay_name' => '批量名称样板 A',
            'merchant_id' => 'batch-name-a',
            'merchant_key' => 'batch-name-a-key',
            'merchant_pem' => 'batch-name-a-pem',
            'pay_check' => 'batch-name-update-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-name-update-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93027,
            'pay_name' => '批量名称样板 B',
            'merchant_id' => 'batch-name-b',
            'merchant_key' => 'batch-name-b-key',
            'merchant_pem' => 'batch-name-b-pem',
            'pay_check' => 'batch-name-update-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-name-update-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-name', [
                'ids_text' => "93026\n93027\n93028",
                'pay_name' => '统一支付名称',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-name?ids=93026,93027,93028');
        $response->assertSessionHas('status', '已批量更新 2 个支付通道的名称');

        $this->assertSame('统一支付名称', DB::table('pays')->where('id', 93026)->value('pay_name'));
        $this->assertSame('统一支付名称', DB::table('pays')->where('id', 93027)->value('pay_name'));
    }

    public function test_batch_name_prefix_page_renders_pay_preview(): void
    {
        DB::table('pays')
            ->whereIn('id', [93029, 93030])
            ->orWhereIn('pay_check', ['batch-prefix-a', 'batch-prefix-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93029,
            'pay_name' => '前缀样板 A',
            'merchant_id' => 'batch-prefix-a',
            'merchant_key' => 'batch-prefix-a-key',
            'merchant_pem' => 'batch-prefix-a-pem',
            'pay_check' => 'batch-prefix-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-prefix-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93030,
            'pay_name' => '前缀样板 B',
            'merchant_id' => 'batch-prefix-b',
            'merchant_key' => 'batch-prefix-b-key',
            'merchant_pem' => 'batch-prefix-b-pem',
            'pay_check' => 'batch-prefix-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-prefix-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-name-prefix?ids=93029,93030,93031');

        $response->assertOk();
        $response->assertSee('批量添加支付名称前缀');
        $response->assertSee('待处理通道数');
        $response->assertSee('前缀样板 A');
        $response->assertSee('前缀样板 B');
        $response->assertSee('93031');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_name_prefix_page_can_update_pay_channels(): void
    {
        DB::table('pays')
            ->whereIn('id', [93032, 93033])
            ->orWhereIn('pay_check', ['batch-prefix-update-a', 'batch-prefix-update-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93032,
            'pay_name' => '前缀样板 A',
            'merchant_id' => 'batch-prefix-a',
            'merchant_key' => 'batch-prefix-a-key',
            'merchant_pem' => 'batch-prefix-a-pem',
            'pay_check' => 'batch-prefix-update-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-prefix-update-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93033,
            'pay_name' => '前缀样板 B',
            'merchant_id' => 'batch-prefix-b',
            'merchant_key' => 'batch-prefix-b-key',
            'merchant_pem' => 'batch-prefix-b-pem',
            'pay_check' => 'batch-prefix-update-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-prefix-update-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-name-prefix', [
                'ids_text' => "93032\n93033\n93034",
                'name_prefix' => '[活动期] ',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-name-prefix?ids=93032,93033,93034');
        $response->assertSessionHas('status', '已批量为 2 个支付通道添加名称前缀');

        $this->assertSame('[活动期]前缀样板 A', DB::table('pays')->where('id', 93032)->value('pay_name'));
        $this->assertSame('[活动期]前缀样板 B', DB::table('pays')->where('id', 93033)->value('pay_name'));
    }

    public function test_batch_name_suffix_page_renders_pay_preview(): void
    {
        DB::table('pays')
            ->whereIn('id', [93035, 93036])
            ->orWhereIn('pay_check', ['batch-suffix-a', 'batch-suffix-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93035,
            'pay_name' => '后缀样板 A',
            'merchant_id' => 'batch-suffix-a',
            'merchant_key' => 'batch-suffix-a-key',
            'merchant_pem' => 'batch-suffix-a-pem',
            'pay_check' => 'batch-suffix-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-suffix-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93036,
            'pay_name' => '后缀样板 B',
            'merchant_id' => 'batch-suffix-b',
            'merchant_key' => 'batch-suffix-b-key',
            'merchant_pem' => 'batch-suffix-b-pem',
            'pay_check' => 'batch-suffix-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-suffix-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-name-suffix?ids=93035,93036,93037');

        $response->assertOk();
        $response->assertSee('批量添加支付名称后缀');
        $response->assertSee('待处理通道数');
        $response->assertSee('后缀样板 A');
        $response->assertSee('后缀样板 B');
        $response->assertSee('93037');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_name_suffix_page_can_update_pay_channels(): void
    {
        DB::table('pays')
            ->whereIn('id', [93035, 93036])
            ->orWhereIn('pay_check', ['batch-suffix-update-a', 'batch-suffix-update-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93035,
            'pay_name' => '后缀样板 A',
            'merchant_id' => 'batch-suffix-a',
            'merchant_key' => 'batch-suffix-a-key',
            'merchant_pem' => 'batch-suffix-a-pem',
            'pay_check' => 'batch-suffix-update-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-suffix-update-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93036,
            'pay_name' => '后缀样板 B',
            'merchant_id' => 'batch-suffix-b',
            'merchant_key' => 'batch-suffix-b-key',
            'merchant_pem' => 'batch-suffix-b-pem',
            'pay_check' => 'batch-suffix-update-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-suffix-update-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-name-suffix', [
                'ids_text' => "93035\n93036\n93037",
                'name_suffix' => ' - 春季活动',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-name-suffix?ids=93035,93036,93037');
        $response->assertSessionHas('status', '已批量为 2 个支付通道添加名称后缀');

        $this->assertSame('后缀样板 A- 春季活动', DB::table('pays')->where('id', 93035)->value('pay_name'));
        $this->assertSame('后缀样板 B- 春季活动', DB::table('pays')->where('id', 93036)->value('pay_name'));
    }

    public function test_batch_name_replace_page_renders_pay_preview(): void
    {
        DB::table('pays')
            ->whereIn('id', [93038, 93039])
            ->orWhereIn('pay_check', ['batch-replace-a', 'batch-replace-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93038,
            'pay_name' => '替换样板 A - 旧标签',
            'merchant_id' => 'batch-replace-a',
            'merchant_key' => 'batch-replace-a-key',
            'merchant_pem' => 'batch-replace-a-pem',
            'pay_check' => 'batch-replace-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-replace-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93039,
            'pay_name' => '替换样板 B - 旧标签',
            'merchant_id' => 'batch-replace-b',
            'merchant_key' => 'batch-replace-b-key',
            'merchant_pem' => 'batch-replace-b-pem',
            'pay_check' => 'batch-replace-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-replace-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/batch-name-replace?ids=93038,93039,93040');

        $response->assertOk();
        $response->assertSee('批量替换支付名称片段');
        $response->assertSee('目标替换内容');
        $response->assertSee('待处理通道数');
        $response->assertSee('替换样板 A - 旧标签');
        $response->assertSee('替换样板 B - 旧标签');
        $response->assertSee('93040');
        $response->assertSee('未匹配 ID 数');
    }

    public function test_batch_name_replace_page_can_update_pay_channels(): void
    {
        DB::table('pays')
            ->whereIn('id', [93041, 93042])
            ->orWhereIn('pay_check', ['batch-replace-update-a', 'batch-replace-update-b'])
            ->delete();

        DB::table('pays')->insert([
            'id' => 93041,
            'pay_name' => '替换样板 A - 旧标签',
            'merchant_id' => 'batch-replace-a',
            'merchant_key' => 'batch-replace-a-key',
            'merchant_pem' => 'batch-replace-a-pem',
            'pay_check' => 'batch-replace-update-a',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/batch-replace-update-a',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 93042,
            'pay_name' => '替换样板 B - 旧标签',
            'merchant_id' => 'batch-replace-b',
            'merchant_key' => 'batch-replace-b-key',
            'merchant_pem' => 'batch-replace-b-pem',
            'pay_check' => 'batch-replace-update-b',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/batch-replace-update-b',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/batch-name-replace', [
                'ids_text' => "93041\n93042\n93043",
                'search_text' => '旧标签',
                'replace_text' => '新标签',
            ]);

        $response->assertRedirect('/admin/v2/pay/batch-name-replace?ids=93041,93042,93043');
        $response->assertSessionHas('status', '已批量替换 2 个支付通道的名称片段');

        $record = DB::table('pays')->where('id', 93041)->first();
        $this->assertSame('替换样板 A - 新标签', $record->pay_name);
        $this->assertSame('batch-replace-update-a', $record->pay_check);
        $this->assertSame('batch-replace-a-key', $record->merchant_key);
        $this->assertSame('/pay/batch-replace-update-a', $record->pay_handleroute);
        $this->assertSame(1, $record->pay_client);
        $this->assertSame(1, $record->pay_method);
        $this->assertSame(1, $record->is_open);
        $this->assertSame('替换样板 B - 新标签', DB::table('pays')->where('id', 93042)->value('pay_name'));
    }

    public function test_edit_page_can_update_pay_channel(): void
    {
        DB::table('pays')->insert([
            'id' => 93003,
            'pay_name' => '支付宝样板',
            'merchant_id' => 'ali-id',
            'merchant_key' => 'ali-key',
            'merchant_pem' => 'ali-pem',
            'pay_check' => 'alipay-shell',
            'pay_client' => 3,
            'pay_handleroute' => '/pay/alipay-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/93003/edit', [
                'pay_name' => '支付宝样板已更新',
                'merchant_id' => 'ali-id-updated',
                'merchant_key' => 'ali-key-updated',
                'merchant_pem' => 'ali-pem-updated',
                'pay_check' => 'alipay-shell',
                'pay_client' => 2,
                'pay_method' => 2,
                'pay_handleroute' => '/pay/alipay-shell-updated',
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/pay/93003/edit');
        $response->assertSessionHas('status', '支付通道已保存');

        $record = DB::table('pays')->where('id', 93003)->first();
        $this->assertSame('支付宝样板已更新', $record->pay_name);
        $this->assertSame('ali-id-updated', $record->merchant_id);
        $this->assertSame('ali-key-updated', $record->merchant_key);
        $this->assertSame('ali-pem-updated', $record->merchant_pem);
        $this->assertSame('/pay/alipay-shell-updated', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(2, $record->pay_method);
        $this->assertSame(0, $record->is_open);
    }

    public function test_edit_page_keeps_existing_secrets_when_left_blank(): void
    {
        DB::table('pays')->insert([
            'id' => 93004,
            'pay_name' => '微信样板',
            'merchant_id' => 'wechat-id',
            'merchant_key' => 'wechat-key',
            'merchant_pem' => 'wechat-pem',
            'pay_check' => 'wechat-shell-blank',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/wechat-shell',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/93004/edit', [
                'pay_name' => '微信样板已更新',
                'merchant_id' => 'wechat-id-updated',
                'merchant_key' => '',
                'merchant_pem' => '',
                'pay_check' => 'wechat-shell-blank',
                'pay_client' => 2,
                'pay_method' => 1,
                'pay_handleroute' => '/pay/wechat-shell-updated',
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/pay/93004/edit');
        $response->assertSessionHas('status', '支付通道已保存');

        $record = DB::table('pays')->where('id', 93004)->first();
        $this->assertSame('微信样板已更新', $record->pay_name);
        $this->assertSame('wechat-id-updated', $record->merchant_id);
        $this->assertSame('wechat-key', $record->merchant_key);
        $this->assertSame('wechat-pem', $record->merchant_pem);
        $this->assertSame('/pay/wechat-shell-updated', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(1, $record->pay_method);
        $this->assertSame(0, $record->is_open);
    }

    private function registerBatchStatusRoutes(): void
    {
        if (Route::getRoutes()->getByName('test.admin-shell.pay.batch-status.edit')) {
            return;
        }

        Route::middleware(config('admin.route.middleware'))
            ->prefix(config('admin.route.prefix'))
            ->group(function () {
                Route::get('v2/pay/actions/batch-status', [PayActionController::class, 'editBatchStatus'])
                    ->name('test.admin-shell.pay.batch-status.edit');
                Route::post('v2/pay/actions/batch-status', [PayActionController::class, 'updateBatchStatus'])
                    ->name('test.admin-shell.pay.batch-status.update');
            });
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
