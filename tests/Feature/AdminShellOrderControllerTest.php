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

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $response = $controller->batchResetSearchPassword(Request::create('/admin/v2/order/batch-reset-search-pwd', 'GET', [
            'ids' => "98001,\n98002,98009",
        ]));

        $this->assertSame('admin-shell.order.batch-reset-search-pwd', $response->name());

        $data = $response->getData();
        $this->assertSame('批量重置订单查询密码 - 后台壳样板', $data['title']);
        $this->assertSame('批量重置订单查询密码', $data['header']['title']);
        $this->assertSame(3, $data['context']['requestedCount']);
        $this->assertSame(2, $data['context']['matchedCount']);
        $this->assertSame([98009], $data['context']['missingIds']);
        $this->assertSame("98001\n98002\n98009", $data['defaults']['ids_text']);
        $this->assertSame('batch-98001', $data['context']['items'][0]['search_pwd']);
        $this->assertSame('batch-98002', $data['context']['items'][1]['search_pwd']);
    }

    public function test_batch_reset_can_refresh_search_passwords_for_matched_orders(): void
    {
        $this->seedBatchOrderFixture(98004, 'batch-98004', '订单批量测试 98004');
        $this->seedBatchOrderFixture(98005, 'batch-98005', '订单批量测试 98005');

        /** @var \App\Http\Controllers\AdminShell\OrderActionController $controller */
        $controller = $this->app->make(OrderActionController::class);
        $response = $controller->updateBatchResetSearchPassword(Request::create('/admin/v2/order/batch-reset-search-pwd', 'POST', [
            'ids_text' => "98004\n98005,98009",
        ]));

        $this->assertStringEndsWith('/admin/v2/order/batch-reset-search-pwd?ids=98004,98005,98009', $response->getTargetUrl());

        $first = Order::query()->findOrFail(98004);
        $second = Order::query()->findOrFail(98005);

        $this->assertNotSame('batch-98004', $first->search_pwd);
        $this->assertNotSame('batch-98005', $second->search_pwd);
        $this->assertStringStartsWith('XG-', $first->search_pwd);
        $this->assertStringStartsWith('XG-', $second->search_pwd);
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
