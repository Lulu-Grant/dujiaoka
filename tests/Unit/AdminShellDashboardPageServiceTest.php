<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Service\AdminShellDashboardPageService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellDashboardPageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Order::query()->forceDelete();
    }

    protected function tearDown(): void
    {
        Order::query()->forceDelete();

        parent::tearDown();
    }

    public function test_build_page_data_returns_dashboard_sections(): void
    {
        $createdAt = now()->startOfDay()->addSecond();

        $this->seedOrder('DASH-SHELL-001', Order::STATUS_COMPLETED, 18.5, $createdAt);
        $this->seedOrder('DASH-SHELL-002', Order::STATUS_PROCESSING, 7.5, $createdAt);
        $this->seedOrder('DASH-SHELL-003', Order::STATUS_WAIT_PAY, 10, $createdAt);

        $page = app(AdminShellDashboardPageService::class)->buildPageData();

        $this->assertSame('后台总览 - 后台壳样板', $page['title']);
        $this->assertSame('后台总览', $page['header']['title']);
        $this->assertCount(4, $page['header']['actions']);
        $this->assertCount(4, $page['cards']);
        $this->assertCount(2, $page['segments']);
        $this->assertCount(9, $page['quick_links']);
        $this->assertCount(3, $page['shortcut_groups']);
        $this->assertCount(3, $page['operator_brief']);
        $this->assertCount(3, $page['operations']);
        $this->assertSame('健康', $page['health']['label']);
        $this->assertSame('good', $page['health']['tone']);
        $this->assertSame(3, $page['hero']['order_count']);
        $this->assertSame('账号设置', $page['quick_links'][0]['label']);
        $this->assertSame('系统设置分组', $page['quick_links'][1]['label']);
        $this->assertSame('账号与系统', $page['shortcut_groups'][0]['title']);
        $this->assertSame('高频管理页', $page['shortcut_groups'][1]['title']);
    }

    private function seedOrder(string $orderSn, int $status, float $actualPrice, $createdAt): void
    {
        DB::table('orders')->insert([
            'order_sn' => $orderSn,
            'goods_id' => 1,
            'coupon_id' => 0,
            'title' => 'Dashboard Product x 1',
            'type' => 1,
            'goods_price' => $actualPrice,
            'buy_amount' => 1,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => $actualPrice,
            'actual_price' => $actualPrice,
            'search_pwd' => 'dashboard',
            'email' => 'dashboard@example.com',
            'info' => '',
            'pay_id' => null,
            'buy_ip' => '127.0.0.1',
            'trade_no' => '',
            'status' => $status,
            'coupon_ret_back' => 0,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'deleted_at' => null,
        ]);
    }
}
