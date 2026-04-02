<?php

namespace App\Service;

use App\Exceptions\RuleValidationException;
use App\Models\Order;
use App\Models\Pay;

class PayEntryService
{
    /**
     * @var \App\Service\OrderService
     */
    private $orderService;

    /**
     * @var \App\Service\PayService
     */
    private $payService;

    /**
     * @var \App\Service\OrderProcessService
     */
    private $orderProcessService;

    public function __construct()
    {
        $this->orderService = app('Service\OrderService');
        $this->payService = app('Service\PayService');
        $this->orderProcessService = app('Service\OrderProcessService');
    }

    /**
     * 校验支付前订单
     *
     * @param string $orderSN
     * @return Order
     * @throws RuleValidationException
     */
    public function requirePayableOrder(string $orderSN): Order
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_does_not_exist'));
        }
        if ($order->status == Order::STATUS_EXPIRED) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_is_expired'));
        }
        if ($order->status > Order::STATUS_WAIT_PAY) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_already_paid'));
        }

        return $order;
    }

    /**
     * 加载支付网关并写回订单
     *
     * @param string $orderSN
     * @param string $payCheck
     * @return array{0: Order, 1: Pay}
     * @throws RuleValidationException
     */
    public function loadGatewayForOrder(string $orderSN, string $payCheck): array
    {
        $order = $this->requirePayableOrder($orderSN);
        $payGateway = $this->payService->detailByCheck($payCheck);
        if (!$payGateway) {
            if (Pay::isRetiredGateway($payCheck)) {
                throw new RuleValidationException(__('dujiaoka.prompt.pay_gateway_retired'));
            }
            throw new RuleValidationException(__('dujiaoka.prompt.pay_gateway_does_not_exist'));
        }

        $order->pay_id = $payGateway->id;
        $order->save();

        return [$order, $payGateway];
    }

    /**
     * 零元订单直接完成
     *
     * @param string $orderSN
     * @return Order
     * @throws RuleValidationException
     */
    public function completeFreeOrder(string $orderSN): Order
    {
        $order = $this->requirePayableOrder($orderSN);
        if (bccomp($order->actual_price, 0.00, 2) != 0) {
            throw new RuleValidationException(__('dujiaoka.prompt.order_inconsistent_amounts'));
        }

        return $this->orderProcessService->completedOrder($order->order_sn, 0.00);
    }
}
