<?php

namespace App\Service;

use App\Exceptions\RuleValidationException;
use App\Models\Order;
use Illuminate\Support\Collection;

class OrderQueryService
{
    /**
     * @var \App\Service\OrderService
     */
    private $orderService;

    public function __construct()
    {
        $this->orderService = app('Service\OrderService');
    }

    /**
     * 获取结账页订单
     *
     * @param string $orderSN
     * @return Order
     * @throws RuleValidationException
     */
    public function requireBillOrder(string $orderSN): Order
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (empty($order)) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_does_not_exist'));
        }
        if ($order->status == Order::STATUS_EXPIRED) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_is_expired'));
        }

        return $order;
    }

    /**
     * 获取订单详情页订单
     *
     * @param string $orderSN
     * @return Order
     * @throws RuleValidationException
     */
    public function requireDetailOrder(string $orderSN): Order
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_does_not_exist'));
        }

        return $order;
    }

    /**
     * 订单状态轮询响应
     *
     * @param string $orderSN
     * @return array<string, int|string>
     */
    public function buildStatusPayload(string $orderSN): array
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order || $order->status == Order::STATUS_EXPIRED) {
            return ['msg' => 'expired', 'code' => 400001];
        }
        if ($order->status == Order::STATUS_WAIT_PAY) {
            return ['msg' => 'wait....', 'code' => 400000];
        }

        return ['msg' => 'success', 'code' => 200];
    }

    /**
     * 通过邮箱和查询密码获取订单
     *
     * @param string $email
     * @param string $searchPwd
     * @return Collection
     * @throws RuleValidationException
     */
    public function requireOrdersByEmail(string $email, string $searchPwd = ''): Collection
    {
        $orders = $this->orderService->withEmailAndPassword($email, $searchPwd);
        if (!$orders || $orders->isEmpty()) {
            throw new RuleValidationException(__('dujiaoka.prompt.no_related_order_found'));
        }

        return $orders;
    }

    /**
     * 通过浏览器缓存获取订单
     *
     * @param string|null $cookieValue
     * @return Collection
     * @throws RuleValidationException
     */
    public function requireOrdersByBrowser(?string $cookieValue): Collection
    {
        if (empty($cookieValue)) {
            throw new RuleValidationException(__('dujiaoka.prompt.no_related_order_found_for_cache'));
        }

        $orderSNS = json_decode($cookieValue, true);
        $orders = $this->orderService->byOrderSNS($orderSNS);

        return $orders;
    }
}
