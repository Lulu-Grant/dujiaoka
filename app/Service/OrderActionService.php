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
}
