<?php

namespace App\Service;

class AdminShellDashboardPageService
{
    /**
     * @var \App\Service\AdminDashboardMetricsService
     */
    private $metricsService;

    public function __construct(AdminDashboardMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function buildPageData(): array
    {
        $successRate = $this->metricsService->successRateSummary('today');
        $sales = $this->metricsService->salesSummary('today');
        $successOrders = $this->metricsService->successOrderSummary('today');
        $payout = $this->metricsService->payoutSummary('today');

        return [
            'title' => '后台总览 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Dashboard',
                'title' => '后台总览',
                'description' => '这是后台壳中的首页看板样板。当前复用已有统计服务，用普通 Laravel 视图承接订单成功率、销售额、已完成订单和支付状态的核心概览。',
                'meta' => '数据口径暂时与旧后台保持一致，优先验证后台壳对首页统计页的承载能力',
                'actions' => [
                    ['label' => '查看订单管理', 'href' => admin_url('v2/order')],
                    ['label' => '进入旧版首页', 'href' => admin_url('/'), 'variant' => 'primary'],
                ],
            ],
            'hero' => [
                'success_rate' => $successRate['success_rate'],
                'order_count' => $successRate['order_count'],
                'completed_count' => $successRate['status_totals']['completed'],
                'sales_total' => number_format((float) $sales['total_price'], 2, '.', ''),
            ],
            'cards' => [
                [
                    'eyebrow' => 'Success Rate',
                    'title' => '今日支付成功率',
                    'value' => $successRate['success_rate'].'%',
                    'description' => '今日共收集 '.$successRate['order_count'].' 笔订单，其中 '.$successRate['status_totals']['completed'].' 笔已完成。',
                    'accent' => 'lime',
                ],
                [
                    'eyebrow' => 'Sales',
                    'title' => '今日销售额',
                    'value' => $heroSales = number_format((float) $sales['total_price'], 2, '.', ''),
                    'description' => '统计范围内已进入履约链的订单销售额总和。',
                    'accent' => 'amber',
                ],
                [
                    'eyebrow' => 'Completed',
                    'title' => '今日完成订单',
                    'value' => (string) $successOrders['success_count'],
                    'description' => '当前口径仅统计已完成订单。',
                    'accent' => 'teal',
                ],
                [
                    'eyebrow' => 'Payout',
                    'title' => '支付状态分布',
                    'value' => $payout['success'].' / '.$payout['unpaid'],
                    'description' => '左侧为已进入支付后链路订单，右侧为待支付订单。',
                    'accent' => 'rose',
                ],
            ],
            'segments' => [
                [
                    'title' => '订单状态分布',
                    'items' => [
                        ['label' => '待处理', 'value' => $successRate['status_totals']['pending']],
                        ['label' => '处理中', 'value' => $successRate['status_totals']['processing']],
                        ['label' => '已完成', 'value' => $successRate['status_totals']['completed']],
                        ['label' => '失败', 'value' => $successRate['status_totals']['failure']],
                        ['label' => '异常', 'value' => $successRate['status_totals']['abnormal']],
                    ],
                ],
                [
                    'title' => '支付状态概览',
                    'items' => [
                        ['label' => '成功链路', 'value' => $payout['success']],
                        ['label' => '待支付', 'value' => $payout['unpaid']],
                    ],
                ],
            ],
        ];
    }
}
