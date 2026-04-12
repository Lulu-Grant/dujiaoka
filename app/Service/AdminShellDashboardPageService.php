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
        $health = $this->buildHealthOverview($successRate, $payout);

        return [
            'title' => '后台总览 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Dashboard',
                'title' => '后台总览',
                'description' => '这是后台壳中的首页控制台。这里优先呈现健康状态、快捷入口和运营概览，让首页先从“看数据”升级成“指挥中心”。',
                'meta' => '当前数据口径与旧后台保持一致，但展示层已经切换为更适合日常巡检的控制台布局。',
                'actions' => [
                    ['label' => '查看订单管理', 'href' => admin_url('v2/order')],
                    ['label' => '查看商品管理', 'href' => admin_url('v2/goods')],
                ],
            ],
            'hero' => [
                'success_rate' => $successRate['success_rate'],
                'order_count' => $successRate['order_count'],
                'completed_count' => $successRate['status_totals']['completed'],
                'sales_total' => number_format((float) $sales['total_price'], 2, '.', ''),
                'health_label' => $health['label'],
                'health_score' => $health['score'],
                'health_note' => $health['note'],
            ],
            'quick_links' => $this->buildQuickLinks(),
            'health' => $health,
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
            'operations' => [
                [
                    'label' => '待处理订单',
                    'value' => $successRate['status_totals']['pending'],
                    'note' => '优先处理等待支付或等待确认的订单。',
                ],
                [
                    'label' => '处理中订单',
                    'value' => $successRate['status_totals']['processing'],
                    'note' => '这些订单已经进入履约链，适合重点巡检。',
                ],
                [
                    'label' => '异常订单',
                    'value' => $successRate['status_totals']['abnormal'],
                    'note' => '这里是今天最需要优先排查的风险点。',
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

    private function buildQuickLinks(): array
    {
        return [
            [
                'label' => '订单管理',
                'description' => '查看待处理、处理中和已完成订单。',
                'href' => admin_url('v2/order'),
            ],
            [
                'label' => '商品管理',
                'description' => '检查商品价格、库存和上下架状态。',
                'href' => admin_url('v2/goods'),
            ],
            [
                'label' => '卡密管理',
                'description' => '导入、编辑和巡检卡密库存。',
                'href' => admin_url('v2/carmis'),
            ],
            [
                'label' => '优惠码管理',
                'description' => '查看折扣策略和使用次数。',
                'href' => admin_url('v2/coupon'),
            ],
            [
                'label' => '支付通道',
                'description' => '确认支付配置与回调入口。',
                'href' => admin_url('v2/pay'),
            ],
            [
                'label' => '系统设置',
                'description' => '调整订单、品牌和通知配置。',
                'href' => admin_url('v2/system-setting'),
            ],
            [
                'label' => '邮件测试',
                'description' => '快速验证发信链路是否正常。',
                'href' => admin_url('v2/email-test'),
            ],
        ];
    }

    private function buildHealthOverview(array $successRate, array $payout): array
    {
        $score = 100;
        $notes = [];

        if ($successRate['order_count'] === 0) {
            $score -= 24;
            $notes[] = '当前统计窗口内暂无订单';
        }

        if ($successRate['status_totals']['abnormal'] > 0) {
            $score -= min(30, $successRate['status_totals']['abnormal'] * 8);
            $notes[] = '存在异常订单需要优先排查';
        }

        if ($successRate['status_totals']['pending'] > 0) {
            $score -= min(16, $successRate['status_totals']['pending'] * 2);
            $notes[] = '有待处理订单';
        }

        if ($payout['unpaid'] > 0) {
            $score -= min(12, $payout['unpaid'] * 2);
            $notes[] = '存在待支付订单';
        }

        if ($successRate['status_totals']['processing'] > 0) {
            $score -= min(8, $successRate['status_totals']['processing'] * 2);
        }

        $score = max(40, min(100, $score));

        if ($score >= 90) {
            $label = '健康';
            $tone = 'good';
        } elseif ($score >= 75) {
            $label = '观察';
            $tone = 'warning';
        } else {
            $label = '关注';
            $tone = 'danger';
        }

        return [
            'label' => $label,
            'tone' => $tone,
            'score' => $score,
            'note' => $notes ? implode('；', $notes) : '当前没有明显风险点，首页状态保持平稳。',
        ];
    }
}
