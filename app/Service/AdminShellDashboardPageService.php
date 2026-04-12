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
        $shortcutGroups = $this->buildShortcutGroups();

        return [
            'title' => '后台总览 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Dashboard',
                'title' => '后台总览',
                'description' => '这是后台壳中的首页控制台。这里优先呈现健康状态、账号设置、系统设置分组和高频管理页，让首页先从“看数据”升级成“指挥中心”。',
                'meta' => '当前数据口径与旧后台保持一致，但展示层已经切换为更适合日常巡检的控制台布局。优先从账号设置、系统设置分组和高频管理页开始操作。',
                'actions' => [
                    ['label' => '账号设置', 'href' => admin_url('auth/setting')],
                    ['label' => '系统设置分组', 'href' => admin_url('v2/system-setting')],
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
            'shortcut_groups' => $shortcutGroups,
            'operator_brief' => $this->buildOperatorBrief($shortcutGroups, $health),
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
                    'value' => number_format((float) $sales['total_price'], 2, '.', ''),
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
                'label' => '账号设置',
                'description' => '修改昵称、头像和登录密码。',
                'href' => admin_url('auth/setting'),
            ],
            [
                'label' => '系统设置分组',
                'description' => '集中进入基础、品牌、邮件、通知和体验配置。',
                'href' => admin_url('v2/system-setting'),
            ],
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
                'label' => '邮件模板',
                'description' => '维护通知模板和变量说明。',
                'href' => admin_url('v2/emailtpl'),
            ],
            [
                'label' => '邮件测试',
                'description' => '快速验证发信链路是否正常。',
                'href' => admin_url('v2/email-test'),
            ],
        ];
    }

    private function buildShortcutGroups(): array
    {
        return [
            [
                'title' => '账号与系统',
                'description' => '先把登录入口、基础配置和品牌信息收拢好。',
                'items' => [
                    ['label' => '账号设置', 'description' => '昵称、头像、密码', 'href' => admin_url('auth/setting')],
                    ['label' => '系统设置总览', 'description' => '所有配置分组入口', 'href' => admin_url('v2/system-setting')],
                    ['label' => '基础配置', 'description' => '站点标题、语言、过期时间', 'href' => admin_url('v2/system-setting/base')],
                    ['label' => '品牌与 Logo', 'description' => '文本 Logo、图片 Logo、主题', 'href' => admin_url('v2/system-setting/branding')],
                    ['label' => '通知推送', 'description' => 'Server 酱、Telegram、Bark', 'href' => admin_url('v2/system-setting/push')],
                ],
            ],
            [
                'title' => '高频管理页',
                'description' => '日常巡检优先打开这些入口，减少来回找页的成本。',
                'items' => [
                    ['label' => '订单管理', 'description' => '待处理、处理中、已完成', 'href' => admin_url('v2/order')],
                    ['label' => '商品管理', 'description' => '库存、售价、上下架', 'href' => admin_url('v2/goods')],
                    ['label' => '卡密管理', 'description' => '导入、编辑、巡检库存', 'href' => admin_url('v2/carmis')],
                    ['label' => '优惠码管理', 'description' => '折扣、次数、启用状态', 'href' => admin_url('v2/coupon')],
                    ['label' => '支付通道', 'description' => '回调、密钥、启用状态', 'href' => admin_url('v2/pay')],
                ],
            ],
            [
                'title' => '模板与辅助工具',
                'description' => '把模板和辅助入口放在一起，方便值班时快速处理。',
                'items' => [
                    ['label' => '邮件模板', 'description' => '变量、预览、用途', 'href' => admin_url('v2/emailtpl')],
                    ['label' => '邮件测试', 'description' => '快速验证发信链路', 'href' => admin_url('v2/email-test')],
                    ['label' => '商品分类', 'description' => '分类、排序、状态', 'href' => admin_url('v2/goods-group')],
                ],
            ],
        ];
    }

    private function buildOperatorBrief(array $shortcutGroups, array $health): array
    {
        $brief = [
            [
                'title' => '第一步：先看异常订单',
                'description' => '如果健康状态不是“健康”，先进入订单管理处理异常和待支付，再回到总览确认变化。',
            ],
            [
                'title' => '第二步：确认账号与系统设置',
                'description' => '账号设置、系统设置总览、品牌与 Logo、通知推送都应该在问题排查前先确认一遍。',
            ],
            [
                'title' => '第三步：打开高频管理页',
                'description' => '订单、商品、卡密、优惠码和支付通道是日常值守的第一线，建议作为浏览器常驻页。',
            ],
        ];

        if ($health['tone'] === 'danger') {
            $brief[0]['description'] = '健康状态已降到关注级，优先进入订单管理处理异常订单，再确认支付通道是否正常。';
        } elseif ($health['tone'] === 'warning') {
            $brief[0]['description'] = '健康状态进入观察区，优先检查订单管理和待支付订单，再回到总览确认。';
        }

        if (!empty($shortcutGroups[0]['items'][0]['href'])) {
            $brief[1]['description'] = '账号设置和系统设置分组已经单独收拢，品牌、邮件、通知、体验配置都从这里进入。';
        }

        return $brief;
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
