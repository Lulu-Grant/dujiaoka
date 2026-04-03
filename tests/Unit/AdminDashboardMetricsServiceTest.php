<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Service\AdminDashboardMetricsService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardMetricsServiceTest extends TestCase
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

    public function test_success_rate_summary_groups_statuses_and_rate(): void
    {
        $this->seedOrder('DASH-001', Order::STATUS_COMPLETED, 12.5, now()->subHour());
        $this->seedOrder('DASH-002', Order::STATUS_PENDING, 0, now()->subHour());
        $this->seedOrder('DASH-003', Order::STATUS_FAILURE, 0, now()->subHour());

        $summary = app(AdminDashboardMetricsService::class)->successRateSummary('today');

        $this->assertSame(3, $summary['order_count']);
        $this->assertSame(1, $summary['status_totals']['completed']);
        $this->assertSame(1, $summary['status_totals']['pending']);
        $this->assertSame(1, $summary['status_totals']['failure']);
        $this->assertSame(33, $summary['success_rate']);
    }

    public function test_sales_success_and_payout_summaries_share_same_service_boundary(): void
    {
        $createdAt = now()->subHours(2);

        $this->seedOrder('DASH-011', Order::STATUS_COMPLETED, 10.5, $createdAt);
        $this->seedOrder('DASH-012', Order::STATUS_PROCESSING, 7.5, $createdAt);
        $this->seedOrder('DASH-013', Order::STATUS_WAIT_PAY, 99, $createdAt);

        $service = app(AdminDashboardMetricsService::class);

        $sales = $service->salesSummary('today');
        $successOrders = $service->successOrderSummary('today');
        $payout = $service->payoutSummary('today');

        $this->assertSame(18.0, $sales['total_price']);
        $this->assertSame([18.0], array_values($sales['series']));
        $this->assertSame(1, $successOrders['success_count']);
        $this->assertSame([1], array_values($successOrders['series']));
        $this->assertSame(2, $payout['success']);
        $this->assertSame(1, $payout['unpaid']);
        $this->assertSame([2, 1], $payout['series']);
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
