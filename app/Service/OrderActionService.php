<?php

namespace App\Service;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderActionService
{
    public function batchStatusDefaults(array $orderIds = []): array
    {
        return [
            'order_ids' => $orderIds,
            'ids_text' => implode("\n", $orderIds),
            'status' => Order::STATUS_PENDING,
        ];
    }

    public function batchTypeDefaults(array $orderIds = []): array
    {
        return [
            'order_ids' => $orderIds,
            'ids_text' => implode("\n", $orderIds),
            'type' => Order::AUTOMATIC_DELIVERY,
        ];
    }

    public function batchStatusContext(array $orderIds): array
    {
        $orders = Order::query()
            ->with(['goods:id,gd_name', 'pay:id,pay_name'])
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get([
                'id',
                'order_sn',
                'title',
                'type',
                'email',
                'status',
                'actual_price',
                'updated_at',
                'goods_id',
                'pay_id',
            ]);

        $matchedIds = $orders->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingIds = array_values(array_diff($orderIds, $matchedIds));

        return [
            'requestedCount' => count($orderIds),
            'matchedCount' => $orders->count(),
            'missingCount' => count($missingIds),
            'matchedIds' => $matchedIds,
            'missingIds' => $missingIds,
            'items' => $orders->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_sn' => $order->order_sn,
                    'title' => $order->title,
                    'email' => $order->email,
                    'status' => $this->statusLabel($order->status),
                    'type' => $this->typeLabel($order->type),
                    'actual_price' => (string) $order->actual_price,
                    'goods' => optional($order->goods)->gd_name ?: '未关联商品',
                    'pay' => optional($order->pay)->pay_name ?: '未选择支付',
                    'updated_at' => (string) $order->updated_at,
                ];
            })->all(),
        ];
    }

    public function parseOrderIds(string $idsText): array
    {
        $tokens = preg_split('/[\s,，]+/u', trim($idsText), -1, PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            return [];
        }

        $parsed = [];

        foreach ($tokens as $token) {
            if (!ctype_digit($token)) {
                continue;
            }

            $id = (int) $token;

            if ($id > 0) {
                $parsed[$id] = $id;
            }
        }

        return array_values($parsed);
    }

    public function batchResetDefaults(array $orderIds = []): array
    {
        return [
            'order_ids' => $orderIds,
            'ids_text' => implode("\n", $orderIds),
        ];
    }

    public function updateTypes(array $orderIds, int $type): int
    {
        if (empty($orderIds)) {
            return 0;
        }

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($orders as $order) {
            $order->type = $type;

            Order::withoutEvents(function () use ($order) {
                $order->save();
            });

            $updated++;
        }

        return $updated;
    }

    public function batchResetContext(array $orderIds): array
    {
        $orders = Order::query()
            ->with(['goods:id,gd_name', 'pay:id,pay_name'])
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get([
                'id',
                'order_sn',
                'title',
                'email',
                'status',
                'search_pwd',
                'updated_at',
                'goods_id',
                'pay_id',
            ]);

        $matchedIds = $orders->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingIds = array_values(array_diff($orderIds, $matchedIds));

        return [
            'requestedCount' => count($orderIds),
            'matchedCount' => $orders->count(),
            'missingCount' => count($missingIds),
            'matchedIds' => $matchedIds,
            'missingIds' => $missingIds,
            'items' => $orders->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_sn' => $order->order_sn,
                    'title' => $order->title,
                    'email' => $order->email,
                    'status' => $this->statusLabel($order->status),
                    'search_pwd' => $order->search_pwd ?: '未设置',
                    'goods' => optional($order->goods)->gd_name ?: '未关联商品',
                    'pay' => optional($order->pay)->pay_name ?: '未选择支付',
                    'updated_at' => (string) $order->updated_at,
                ];
            })->all(),
        ];
    }

    public function batchResetSearchPasswords(array $orderIds): int
    {
        if (empty($orderIds)) {
            return 0;
        }

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($orders as $order) {
            $this->resetSearchPassword($order);
            $updated++;
        }

        return $updated;
    }

    public function updateStatuses(array $orderIds, int $status): int
    {
        if (empty($orderIds)) {
            return 0;
        }

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($orders as $order) {
            $order->status = $status;

            Order::withoutEvents(function () use ($order) {
                $order->save();
            });

            $updated++;
        }

        return $updated;
    }

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
            'summarySections' => [
                [
                    'title' => '基础信息',
                    'description' => '先确认订单主体、联系邮箱和当前状态，避免误改对象。',
                    'items' => [
                        ['label' => '订单号', 'value' => $order->order_sn],
                        ['label' => '订单标题', 'value' => $order->title],
                        ['label' => '邮箱', 'value' => $order->email],
                        ['label' => '订单状态', 'value' => $this->statusLabel($order->status)],
                        ['label' => '订单类型', 'value' => $this->typeLabel($order->type)],
                    ],
                ],
                [
                    'title' => '交易与履约',
                    'description' => '把商品、支付、金额和交易号放在一起，便于核对支付链上下文。',
                    'items' => [
                        ['label' => '关联商品', 'value' => optional($order->goods)->gd_name ?: '未关联商品'],
                        ['label' => '支付通道', 'value' => optional($order->pay)->pay_name ?: '未选择支付'],
                        ['label' => '实付金额', 'value' => (string) $order->actual_price],
                        ['label' => '交易号', 'value' => $order->trade_no ?: '未生成'],
                        ['label' => '查询密码', 'value' => $order->search_pwd ?: '未设置'],
                    ],
                ],
                [
                    'title' => '维护提醒',
                    'description' => '下面这些是只读辅助信息，保存时不会触发支付完成或履约链动作。',
                    'items' => [
                        ['label' => '创建时间', 'value' => (string) $order->created_at],
                        ['label' => '更新时间', 'value' => (string) $order->updated_at],
                        ['label' => '安全动作', 'value' => '重置查询密码'],
                        ['label' => '当前维护字段', 'value' => '订单标题、订单附加信息、订单状态、查询密码、订单类型'],
                        ['label' => '保存边界', 'value' => '仅更新人工维护字段，不触发履约、发货或支付回调。'],
                    ],
                ],
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

    public function resetSearchPassword(Order $order): string
    {
        $newPassword = $this->generateSearchPassword();
        $order->search_pwd = $newPassword;

        Order::withoutEvents(function () use ($order) {
            $order->save();
        });

        return $newPassword;
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

    private function generateSearchPassword(): string
    {
        return 'XG-'.Str::upper(Str::random(8));
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
