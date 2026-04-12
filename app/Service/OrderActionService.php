<?php

namespace App\Service;

use App\Models\Order;

class OrderActionService
{
    public function editDefaults(Order $order): array
    {
        return [
            'title' => $order->title,
            'info' => $order->info,
            'status' => $order->status,
            'search_pwd' => $order->search_pwd,
            'type' => $order->type,
        ];
    }

    public function editContext(Order $order): array
    {
        return [
            'summaryTitle' => '当前订单概览',
            'summaryDescription' => '下面这些信息仅用于人工维护时快速确认上下文，不会在本页直接修改。',
            'summaryItems' => [
                ['label' => '订单号', 'value' => $order->order_sn],
                ['label' => '订单状态', 'value' => $this->statusLabel($order->status)],
                ['label' => '订单类型', 'value' => $this->typeLabel($order->type)],
                ['label' => '关联商品', 'value' => optional($order->goods)->gd_name ?: '未关联商品'],
                ['label' => '支付通道', 'value' => optional($order->pay)->pay_name ?: '未选择支付'],
                ['label' => '实付金额', 'value' => (string) $order->actual_price],
                ['label' => '交易号', 'value' => $order->trade_no ?: '未生成'],
                ['label' => '创建时间', 'value' => (string) $order->created_at],
                ['label' => '更新时间', 'value' => (string) $order->updated_at],
            ],
            'editableFields' => [
                '订单标题',
                '订单附加信息',
                '订单状态',
                '查询密码',
                '订单类型',
            ],
            'notice' => '这张页面只承接低风险人工维护字段，保存后不会触发支付完成或履约链动作。',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Order $order, array $payload): void
    {
        $order->title = $payload['title'];
        $order->info = $payload['info'] ?? '';
        $order->status = (int) $payload['status'];
        $order->search_pwd = $payload['search_pwd'];
        $order->type = (int) $payload['type'];

        Order::withoutEvents(function () use ($order) {
            $order->save();
        });
    }

    private function statusLabel(int $status): string
    {
        return Order::getStatusMap()[$status] ?? (string) $status;
    }

    private function typeLabel(int $type): string
    {
        return Order::getTypeMap()[$type] ?? (string) $type;
    }
}
