<?php

namespace App\Service;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardMetricsService
{
    public function successRateSummary(string $option = 'today'): array
    {
        [$startTime, $endTime] = $this->resolveDateRange($option, true);

        $orderGroup = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->select('status', DB::raw('count(id) as num'))
            ->groupBy('status')
            ->pluck('num', 'status')
            ->toArray();

        $orderCount = array_sum($orderGroup);
        $completed = (int) ($orderGroup[Order::STATUS_COMPLETED] ?? 0);
        $successRate = 0;

        if ($orderCount > 0) {
            $successRate = (int) bcmul(bcdiv((string) $completed, (string) $orderCount, 2), '100');
        }

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'order_count' => $orderCount,
            'success_rate' => $successRate,
            'status_totals' => [
                'pending' => (int) ($orderGroup[Order::STATUS_PENDING] ?? 0),
                'processing' => (int) ($orderGroup[Order::STATUS_PROCESSING] ?? 0),
                'completed' => $completed,
                'failure' => (int) ($orderGroup[Order::STATUS_FAILURE] ?? 0),
                'abnormal' => (int) ($orderGroup[Order::STATUS_ABNORMAL] ?? 0),
            ],
        ];
    }

    public function salesSummary(string $option = 'seven'): array
    {
        [$startTime, $endTime] = $this->resolveDateRange($option);

        $series = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', '>', Order::STATUS_PENDING)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(actual_price) as actual_price'))
            ->groupBy('date')
            ->pluck('actual_price')
            ->map(function ($price) {
                return (float) $price;
            })
            ->toArray();

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_price' => array_sum($series),
            'series' => $series,
        ];
    }

    public function successOrderSummary(string $option = 'seven'): array
    {
        [$startTime, $endTime] = $this->resolveDateRange($option);

        $series = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', Order::STATUS_COMPLETED)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as num'))
            ->groupBy('date')
            ->pluck('num')
            ->map(function ($count) {
                return (int) $count;
            })
            ->toArray();

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'success_count' => array_sum($series),
            'series' => $series,
        ];
    }

    public function payoutSummary(string $option = 'seven'): array
    {
        [$startTime, $endTime] = $this->resolveDateRange($option, true);

        $success = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();

        $unpaid = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', '<=', Order::STATUS_WAIT_PAY)
            ->count();

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'success' => $success,
            'unpaid' => $unpaid,
            'series' => [$success, $unpaid],
        ];
    }

    private function resolveDateRange(string $option, bool $allowYear = false): array
    {
        $endTime = Carbon::now();

        switch ($option) {
            case 'seven':
                $startTime = Carbon::now()->subDays(7);
                break;
            case 'month':
                $startTime = Carbon::now()->subDays(30);
                break;
            case 'year':
                $startTime = $allowYear ? Carbon::now()->subDays(365) : Carbon::now()->subDays(7);
                break;
            case 'today':
                $startTime = Carbon::today();
                break;
            default:
                $startTime = $allowYear ? Carbon::today() : Carbon::now()->subDays(7);
        }

        return [$startTime, $endTime];
    }
}
