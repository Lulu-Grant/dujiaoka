<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminShell\OrderActionController;
use App\Models\Order;
use Dcat\Admin\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellOrderControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('orders')->whereIn('id', [
            97001,
            97002,
            97003,
            97004,
            97005,
            97006,
            98001,
            98002,
            98003,
            98004,
            98005,
            98006,
            98007,
            98008,
            98009,
            98014,
            98015,
            98016,
            98017,
            98011,
            98012,
            98013,
            98021,
            98022,
            98023,
            98024,
            98031,
            98032,
            98033,
            98034,
            98041,
            98042,
            98043,
            98044,
            98051,
            98052,
            98053,
            98054,
        ])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_order_page(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order');

        $response->assertOk();
        $response->assertSee('订单管理');
        $response->assertSee('订单号');
        $response->assertSee('筛选');
    }

    public function test_show_renders_order_detail_page(): void
    {
        $id = 97002;
        $this->seedOrderFixture($id);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/'.$id);

        $response->assertOk();
        $response->assertSee('订单详情');
        $response->assertSee('XIGUA-ORDER-'.$id);
        $response->assertSee('基础信息');
        $response->assertSee('商品与支付');
        $response->assertSee('金额与履约');
        $response->assertSee('维护信息');
        $response->assertSee('订单附加信息');
        $response->assertSee('未关联商品');
        $response->assertSee('未选择支付');
    }

    public function test_edit_renders_order_action_form(): void
    {
        $id = 97003;
        $this->seedOrderFixture($id);

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $response = $controller->edit($id);

        $this->assertSame('admin-shell.order.form', $response->name());
        $data = $response->getData();
        $this->assertSame('编辑订单 - 后台壳样板', $data['title']);
        $this->assertSame('编辑订单', $data['header']['title']);
        $this->assertSame('当前订单概览', $data['context']['summaryTitle']);
        $this->assertSame('基础信息', $data['context']['summarySections'][0]['title']);
        $this->assertSame('交易与履约', $data['context']['summarySections'][1]['title']);
        $this->assertSame('维护提醒', $data['context']['summarySections'][2]['title']);
        $this->assertSame('订单标题', $data['context']['summarySections'][0]['items'][1]['label']);
        $this->assertSame('search-me', $data['defaults']['search_pwd']);
    }

    public function test_edit_can_update_order_fields(): void
    {
        $id = 97004;
        $this->seedOrderFixture($id);

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $response = $controller->update($id, Request::create('/admin/v2/order/'.$id.'/edit', 'POST', [
            'title' => '更新后的订单标题',
            'info' => "新的附加信息\n第二行",
            'status' => 2,
            'search_pwd' => 'updated-pwd',
            'type' => 2,
        ]));

        $this->assertStringEndsWith('/admin/v2/order/'.$id.'/edit', $response->getTargetUrl());

        $record = Order::query()->findOrFail($id);
        $record->refresh();

        $this->assertSame('更新后的订单标题', $record->title);
        $this->assertSame("新的附加信息\n第二行", $record->info);
        $this->assertSame(2, $record->status);
        $this->assertSame('updated-pwd', $record->search_pwd);
        $this->assertSame(2, $record->type);
    }

    public function test_edit_can_reset_order_query_password(): void
    {
        $id = 97005;
        $this->seedOrderFixture($id);

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $response = $controller->update($id, Request::create('/admin/v2/order/'.$id.'/edit', 'POST', [
            'reset_search_pwd' => 1,
        ]));

        $this->assertStringEndsWith('/admin/v2/order/'.$id.'/edit', $response->getTargetUrl());

        $record = Order::query()->findOrFail($id);
        $record->refresh();

        $this->assertNotSame('search-me', $record->search_pwd);
        $this->assertStringStartsWith('XG-', $record->search_pwd);
        $this->assertSame('测试订单 Shell '.$id, $record->title);
        $this->assertSame(4, $record->status);
        $this->assertSame(1, $record->type);
    }

    public function test_edit_page_exposes_reset_query_password_button(): void
    {
        $id = 97006;
        $this->seedOrderFixture($id);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/'.$id.'/edit');

        $response->assertOk();
        $response->assertSee('重置查询密码');
    }

    public function test_batch_reset_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98001, 'batch-98001', '订单批量测试 98001');
        $this->seedBatchOrderFixture(98002, 'batch-98002', '订单批量测试 98002');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-reset-search-pwd?ids='.urlencode("98001,\n98002,98009"));

        $response->assertOk();
        $response->assertSee('批量重置订单查询密码');
        $response->assertSee('订单批量测试 98001');
        $response->assertSee('订单批量测试 98002');
        $response->assertSee('98009');

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $view = $controller->batchResetSearchPassword(Request::create('/admin/v2/order/batch-reset-search-pwd', 'GET', [
            'ids' => "98001,\n98002,98009",
        ]));

        $this->assertSame('admin-shell.order.batch-reset-search-pwd', $view->name());

        $data = $view->getData();
        $this->assertSame('批量重置订单查询密码 - 后台壳样板', $data['title']);
        $this->assertSame('批量重置订单查询密码', $data['header']['title']);
        $this->assertSame(3, $data['context']['requestedCount']);
        $this->assertSame(2, $data['context']['matchedCount']);
        $this->assertSame([98009], $data['context']['missingIds']);
        $this->assertSame("98001\n98002\n98009", $data['defaults']['ids_text']);
        $this->assertSame('batch-98001', $data['context']['items'][0]['search_pwd']);
        $this->assertSame('batch-98002', $data['context']['items'][1]['search_pwd']);
    }

    public function test_batch_status_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98006, 'batch-98006', '订单批量测试 98006');
        $this->seedBatchOrderFixture(98007, 'batch-98007', '订单批量测试 98007');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-status?ids='.urlencode("98006,\n98007,98009"));

        $response->assertOk();
        $response->assertSee('批量更新订单状态');
        $response->assertSee('订单批量测试 98006');
        $response->assertSee('订单批量测试 98007');
        $response->assertSee('98009');

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $view = $controller->editBatchStatus(Request::create('/admin/v2/order/batch-status', 'GET', [
            'ids' => "98006,\n98007,98009",
        ]));

        $this->assertSame('admin-shell.order.batch-status', $view->name());

        $data = $view->getData();
        $this->assertSame('批量更新订单状态 - 后台壳样板', $data['title']);
        $this->assertSame('批量更新订单状态', $data['header']['title']);
        $this->assertSame(3, $data['context']['requestedCount']);
        $this->assertSame(2, $data['context']['matchedCount']);
        $this->assertSame([98009], $data['context']['missingIds']);
        $this->assertSame("98006\n98007\n98009", $data['defaults']['ids_text']);
        $this->assertSame('订单批量测试 98006', $data['context']['items'][0]['title']);
        $this->assertSame((string) Order::STATUS_PENDING, (string) $data['defaults']['status']);
    }

    public function test_batch_status_can_update_order_statuses_for_matched_orders(): void
    {
        $this->seedBatchOrderFixture(98008, 'batch-98008', '订单批量测试 98008');
        $this->seedBatchOrderFixture(98009, 'batch-98009', '订单批量测试 98009');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-status', [
                'ids_text' => "98008\n98009,98012",
                'status' => Order::STATUS_PROCESSING,
            ]);

        $response->assertRedirect('/admin/v2/order/batch-status?ids=98008,98009,98012');

        $first = Order::query()->findOrFail(98008);
        $second = Order::query()->findOrFail(98009);

        $this->assertSame(Order::STATUS_PROCESSING, (int) $first->status);
        $this->assertSame(Order::STATUS_PROCESSING, (int) $second->status);
        $this->assertSame('batch-98008', $first->search_pwd);
        $this->assertSame('batch-98009', $second->search_pwd);
    }

    public function test_batch_type_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98014, 'batch-98014', '订单批量测试 98014');
        $this->seedBatchOrderFixture(98015, 'batch-98015', '订单批量测试 98015');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-type?ids='.urlencode("98014,\n98015,98019"));

        $response->assertOk();
        $response->assertSee('批量设置订单类型');
        $response->assertSee('订单批量测试 98014');
        $response->assertSee('订单批量测试 98015');
        $response->assertSee('98019');

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $view = $controller->editBatchType(Request::create('/admin/v2/order/batch-type', 'GET', [
            'ids' => "98014,\n98015,98019",
        ]));

        $this->assertSame('admin-shell.order.batch-type', $view->name());

        $data = $view->getData();
        $this->assertSame('批量设置订单类型 - 后台壳样板', $data['title']);
        $this->assertSame('批量设置订单类型', $data['header']['title']);
        $this->assertSame(3, $data['context']['requestedCount']);
        $this->assertSame(2, $data['context']['matchedCount']);
        $this->assertSame([98019], $data['context']['missingIds']);
        $this->assertSame("98014\n98015\n98019", $data['defaults']['ids_text']);
        $this->assertSame((string) Order::AUTOMATIC_DELIVERY, (string) $data['defaults']['type']);
        $this->assertSame(admin_trans('goods.fields.automatic_delivery'), $data['context']['items'][0]['type']);
    }

    public function test_batch_type_can_update_order_types_for_matched_orders(): void
    {
        $this->seedBatchOrderFixture(98016, 'batch-98016', '订单批量测试 98016');
        $this->seedBatchOrderFixture(98017, 'batch-98017', '订单批量测试 98017');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-type', [
                'ids_text' => "98016\n98017,98020",
                'type' => Order::MANUAL_PROCESSING,
            ]);

        $response->assertRedirect('/admin/v2/order/batch-type?ids=98016,98017,98020');

        $first = Order::query()->findOrFail(98016);
        $second = Order::query()->findOrFail(98017);

        $this->assertSame(Order::MANUAL_PROCESSING, (int) $first->type);
        $this->assertSame(Order::MANUAL_PROCESSING, (int) $second->type);
        $this->assertSame('batch-98016', $first->search_pwd);
        $this->assertSame('batch-98017', $second->search_pwd);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $first->status);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $second->status);
    }

    public function test_batch_info_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98021, 'batch-98021', '订单批量测试 98021');
        $this->seedBatchOrderFixture(98022, 'batch-98022', '订单批量测试 98022');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-info?ids='.urlencode("98021,\n98022,98029"));

        $response->assertOk();
        $response->assertSee('批量设置订单附加信息');
        $response->assertSee('订单批量测试 98021');
        $response->assertSee('订单批量测试 98022');
        $response->assertSee('98029');

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $view = $controller->editBatchInfo(Request::create('/admin/v2/order/batch-info', 'GET', [
            'ids' => "98021,\n98022,98029",
        ]));

        $this->assertSame('admin-shell.order.batch-info', $view->name());

        $data = $view->getData();
        $this->assertSame('批量设置订单附加信息 - 后台壳样板', $data['title']);
        $this->assertSame('批量设置订单附加信息', $data['header']['title']);
        $this->assertSame(3, $data['context']['requestedCount']);
        $this->assertSame(2, $data['context']['matchedCount']);
        $this->assertSame([98029], $data['context']['missingIds']);
        $this->assertSame("98021\n98022\n98029", $data['defaults']['ids_text']);
        $this->assertSame('', $data['defaults']['info']);
    }

    public function test_batch_info_can_update_order_infos_without_touching_status_or_search_password(): void
    {
        $this->seedBatchOrderFixture(98023, 'batch-98023', '订单批量测试 98023');
        $this->seedBatchOrderFixture(98024, 'batch-98024', '订单批量测试 98024');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-info', [
                'ids_text' => "98023\n98024,98030",
                'info' => '2026 春季活动人工复核',
            ]);

        $response->assertRedirect('/admin/v2/order/batch-info?ids=98023,98024,98030');
        $response->assertSessionHas('status', '已批量更新 2 个订单的附加信息');

        $first = Order::query()->findOrFail(98023);
        $second = Order::query()->findOrFail(98024);

        $this->assertSame('2026 春季活动人工复核', $first->info);
        $this->assertSame('2026 春季活动人工复核', $second->info);
        $this->assertSame('batch-98023', $first->search_pwd);
        $this->assertSame('batch-98024', $second->search_pwd);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $first->status);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $second->status);
    }

    public function test_batch_title_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98031, 'batch-98031', '订单批量测试 98031');
        $this->seedBatchOrderFixture(98032, 'batch-98032', '订单批量测试 98032');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-title?ids='.urlencode("98031,\n98032,98039"));

        $response->assertOk();
        $response->assertSee('批量设置订单标题');
        $response->assertSee('目标标题');
        $response->assertSee('订单批量测试 98031');
        $response->assertSee('订单批量测试 98032');
        $response->assertSee('98039');
    }

    public function test_batch_title_can_update_order_titles_without_touching_status_or_search_password(): void
    {
        $this->seedBatchOrderFixture(98033, 'batch-98033', '订单批量测试 98033');
        $this->seedBatchOrderFixture(98034, 'batch-98034', '订单批量测试 98034');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-title', [
                'ids_text' => "98033\n98034,98040",
                'title' => '2026 春季活动人工复核单',
            ]);

        $response->assertRedirect('/admin/v2/order/batch-title?ids=98033,98034,98040');
        $response->assertSessionHas('status', '已批量更新 2 个订单的标题');

        $first = Order::query()->findOrFail(98033);
        $second = Order::query()->findOrFail(98034);

        $this->assertSame('2026 春季活动人工复核单', $first->title);
        $this->assertSame('2026 春季活动人工复核单', $second->title);
        $this->assertSame('batch-98033', $first->search_pwd);
        $this->assertSame('batch-98034', $second->search_pwd);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $first->status);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $second->status);
    }

    public function test_batch_title_prefix_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98041, 'batch-98041', '订单批量测试 98041');
        $this->seedBatchOrderFixture(98042, 'batch-98042', '订单批量测试 98042');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-title-prefix?ids='.urlencode("98041,\n98042,98049"));

        $response->assertOk();
        $response->assertSee('批量添加订单标题前缀');
        $response->assertSee('目标标题前缀');
        $response->assertSee('订单批量测试 98041');
        $response->assertSee('订单批量测试 98042');
        $response->assertSee('98049');
    }

    public function test_batch_title_prefix_can_update_order_titles_without_touching_status_or_search_password(): void
    {
        $this->seedBatchOrderFixture(98043, 'batch-98043', '订单批量测试 98043');
        $this->seedBatchOrderFixture(98044, 'batch-98044', '订单批量测试 98044');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-title-prefix', [
                'ids_text' => "98043\n98044,98050",
                'title_prefix' => '[人工复核]-',
            ]);

        $response->assertRedirect('/admin/v2/order/batch-title-prefix?ids=98043,98044,98050');
        $response->assertSessionHas('status', '已批量为 2 个订单标题添加前缀');

        $first = Order::query()->findOrFail(98043);
        $second = Order::query()->findOrFail(98044);

        $this->assertSame('[人工复核]-订单批量测试 98043', $first->title);
        $this->assertSame('[人工复核]-订单批量测试 98044', $second->title);
        $this->assertSame('batch-98043', $first->search_pwd);
        $this->assertSame('batch-98044', $second->search_pwd);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $first->status);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $second->status);
    }

    public function test_batch_title_suffix_page_renders_matching_preview(): void
    {
        $this->seedBatchOrderFixture(98051, 'batch-98051', '订单批量测试 98051');
        $this->seedBatchOrderFixture(98052, 'batch-98052', '订单批量测试 98052');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order/batch-title-suffix?ids='.urlencode("98051,\n98052,98059"));

        $response->assertOk();
        $response->assertSee('批量添加订单标题后缀');
        $response->assertSee('目标标题后缀');
        $response->assertSee('订单批量测试 98051');
        $response->assertSee('订单批量测试 98052');
        $response->assertSee('98059');
    }

    public function test_batch_title_suffix_can_update_order_titles_without_touching_status_or_search_password(): void
    {
        $this->seedBatchOrderFixture(98053, 'batch-98053', '订单批量测试 98053');
        $this->seedBatchOrderFixture(98054, 'batch-98054', '订单批量测试 98054');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-title-suffix', [
                'ids_text' => "98053\n98054,98060",
                'title_suffix' => '-已复核',
            ]);

        $response->assertRedirect('/admin/v2/order/batch-title-suffix?ids=98053,98054,98060');
        $response->assertSessionHas('status', '已批量为 2 个订单标题添加后缀');

        $first = Order::query()->findOrFail(98053);
        $second = Order::query()->findOrFail(98054);

        $this->assertSame('订单批量测试 98053-已复核', $first->title);
        $this->assertSame('订单批量测试 98054-已复核', $second->title);
        $this->assertSame('batch-98053', $first->search_pwd);
        $this->assertSame('batch-98054', $second->search_pwd);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $first->status);
        $this->assertSame(Order::STATUS_COMPLETED, (int) $second->status);
    }

    public function test_batch_reset_can_refresh_search_passwords_for_matched_orders(): void
    {
        $this->seedBatchOrderFixture(98004, 'batch-98004', '订单批量测试 98004');
        $this->seedBatchOrderFixture(98005, 'batch-98005', '订单批量测试 98005');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/order/batch-reset-search-pwd', [
            'ids_text' => "98004\n98005,98009",
        ]);

        $response->assertRedirect('/admin/v2/order/batch-reset-search-pwd?ids=98004,98005,98009');

        $first = Order::query()->findOrFail(98004);
        $second = Order::query()->findOrFail(98005);

        $this->assertNotSame('batch-98004', $first->search_pwd);
        $this->assertNotSame('batch-98005', $second->search_pwd);
        $this->assertStringStartsWith('XG-', $first->search_pwd);
        $this->assertStringStartsWith('XG-', $second->search_pwd);
    }

    public function test_index_exposes_export_actions(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order');

        $response->assertOk();
        $response->assertSee('批量更新订单状态');
        $response->assertSee('批量设置订单类型');
        $response->assertSee('批量设置附加信息');
        $response->assertSee('导出文本');
        $response->assertSee('导出 CSV');
    }

    public function test_index_can_export_text_for_current_filters(): void
    {
        DB::table('orders')->where('order_sn', 'like', 'XIGUA-EXPORT-%')->delete();
        $this->seedExportOrderFixture(98011, 'Export Alpha Shell');
        $this->seedExportOrderFixture(98012, 'Export Beta Shell');
        $this->seedExportOrderFixture(98013, 'Other Title');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order?title='.urlencode('Export').'&export=text');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $response->assertSee('订单导出');
        $response->assertSee('筛选条件：标题=Export');
        $response->assertSee('导出数量：2');
        $response->assertSee('Export Alpha Shell');
        $response->assertSee('Export Beta Shell');
        $response->assertDontSee('Other Title');
    }

    public function test_index_can_export_csv_for_current_filters(): void
    {
        DB::table('orders')->where('order_sn', 'like', 'XIGUA-EXPORT-%')->delete();
        $this->seedExportOrderFixture(98021, 'CSV Export Alpha');
        $this->seedExportOrderFixture(98022, 'CSV Export Beta');
        $this->seedExportOrderFixture(98023, 'Different Title');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/order?title='.urlencode('CSV Export').'&export=csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $response->assertSee('订单号');
        $response->assertSee('CSV Export Alpha');
        $response->assertSee('CSV Export Beta');
        $response->assertDontSee('Different Title');
    }

    private function seedOrderFixture(int $id): void
    {
        DB::table('orders')->where('id', $id)->delete();
        DB::table('orders')->insert([
            'id' => $id,
            'order_sn' => 'XIGUA-ORDER-'.$id,
            'title' => '测试订单 Shell '.$id,
            'type' => 1,
            'email' => 'shell@example.com',
            'goods_id' => 0,
            'goods_price' => 79,
            'buy_amount' => 1,
            'total_price' => 79,
            'coupon_id' => 0,
            'coupon_discount_price' => 10,
            'wholesale_discount_price' => 0,
            'actual_price' => 69,
            'pay_id' => 0,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => 'search-me',
            'trade_no' => 'trade-no-shell',
            'status' => 4,
            'info' => "账号: demo@example.com {$id}\n密码: 123456 {$id}",
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function seedExportOrderFixture(int $id, string $title): void
    {
        DB::table('orders')->where('id', $id)->delete();
        DB::table('orders')->insert([
            'id' => $id,
            'order_sn' => 'XIGUA-EXPORT-'.$id,
            'title' => $title,
            'type' => 1,
            'email' => 'export@example.com',
            'goods_id' => 0,
            'goods_price' => 88,
            'buy_amount' => 1,
            'total_price' => 88,
            'coupon_id' => 0,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'actual_price' => 88,
            'pay_id' => 0,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => 'export-pwd-'.$id,
            'trade_no' => 'trade-no-export-'.$id,
            'status' => 4,
            'info' => "导出测试 {$id}",
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function seedBatchOrderFixture(int $id, string $searchPwd, string $title): void
    {
        DB::table('orders')->where('id', $id)->delete();
        DB::table('orders')->insert([
            'id' => $id,
            'order_sn' => 'XIGUA-BATCH-'.$id,
            'title' => $title,
            'type' => 1,
            'email' => 'batch@example.com',
            'goods_id' => 0,
            'goods_price' => 79,
            'buy_amount' => 1,
            'total_price' => 79,
            'coupon_id' => 0,
            'coupon_discount_price' => 10,
            'wholesale_discount_price' => 0,
            'actual_price' => 69,
            'pay_id' => 0,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => $searchPwd,
            'trade_no' => 'trade-no-batch-'.$id,
            'status' => 4,
            'info' => "账号: demo@example.com {$id}\n密码: 123456 {$id}",
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();
        DB::table('admin_users')->insert([
            'username' => 'admin-shell-tester',
            'password' => bcrypt('secret123'),
            'name' => 'Admin Shell Tester',
            'avatar' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Administrator::query()->where('username', 'admin-shell-tester')->firstOrFail();
    }
}
