<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellDashboardControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('orders')->whereIn('order_sn', ['DASH-FEATURE-001', 'DASH-FEATURE-002', 'DASH-FEATURE-003'])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_dashboard_renders_admin_shell_overview_page(): void
    {
        $this->seedDashboardOrders();

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/dashboard');

        $response->assertOk();
        $response->assertSee('后台总览');
        $response->assertSee('首页先变成指挥台，再慢慢替代旧后台');
        $response->assertSee('系统健康状态');
        $response->assertSee('快捷入口');
        $response->assertSee('运营视图');
        $response->assertSee('订单状态分布');
    }

    public function test_admin_home_redirects_to_admin_shell_dashboard(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin');

        $response->assertRedirect('/admin/v2/dashboard');
    }

    private function seedDashboardOrders(): void
    {
        $timestamp = now()->startOfDay()->addMinute();

        DB::table('orders')->insert([
            [
                'order_sn' => 'DASH-FEATURE-001',
                'goods_id' => 1,
                'coupon_id' => 0,
                'title' => 'Dashboard Product x 1',
                'type' => 1,
                'goods_price' => 12.5,
                'buy_amount' => 1,
                'coupon_discount_price' => 0,
                'wholesale_discount_price' => 0,
                'total_price' => 12.5,
                'actual_price' => 12.5,
                'search_pwd' => 'dashboard',
                'email' => 'dashboard@example.com',
                'info' => '',
                'pay_id' => null,
                'buy_ip' => '127.0.0.1',
                'trade_no' => '',
                'status' => 4,
                'coupon_ret_back' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'order_sn' => 'DASH-FEATURE-002',
                'goods_id' => 1,
                'coupon_id' => 0,
                'title' => 'Dashboard Product x 1',
                'type' => 1,
                'goods_price' => 9.5,
                'buy_amount' => 1,
                'coupon_discount_price' => 0,
                'wholesale_discount_price' => 0,
                'total_price' => 9.5,
                'actual_price' => 9.5,
                'search_pwd' => 'dashboard',
                'email' => 'dashboard@example.com',
                'info' => '',
                'pay_id' => null,
                'buy_ip' => '127.0.0.1',
                'trade_no' => '',
                'status' => 3,
                'coupon_ret_back' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'order_sn' => 'DASH-FEATURE-003',
                'goods_id' => 1,
                'coupon_id' => 0,
                'title' => 'Dashboard Product x 1',
                'type' => 1,
                'goods_price' => 99,
                'buy_amount' => 1,
                'coupon_discount_price' => 0,
                'wholesale_discount_price' => 0,
                'total_price' => 99,
                'actual_price' => 99,
                'search_pwd' => 'dashboard',
                'email' => 'dashboard@example.com',
                'info' => '',
                'pay_id' => null,
                'buy_ip' => '127.0.0.1',
                'trade_no' => '',
                'status' => 1,
                'coupon_ret_back' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
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
