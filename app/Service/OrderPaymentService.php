<?php

namespace App\Service;

use App\Models\Order;

class OrderPaymentService
{
    /**
     * @var \App\Service\OrderService
     */
    private $orderService;

    /**
     * @var \App\Service\GoodsService
     */
    private $goodsService;

    /**
     * @var \App\Service\OrderFulfillmentService
     */
    private $orderFulfillmentService;

    public function __construct()
    {
        $this->orderService = app('Service\OrderService');
        $this->goodsService = app('Service\GoodsService');
        $this->orderFulfillmentService = app(OrderFulfillmentService::class);
    }

    /**
     * 支付完成后的核心状态流转
     *
     * @param string $orderSN
     * @param float $actualPrice
     * @param string $tradeNo
     * @return Order
     * @throws \Exception
     */
    public function completePayment(string $orderSN, float $actualPrice, string $tradeNo = ''): Order
    {
        $order = $this->validateCompletableOrder($orderSN, $actualPrice);
        $this->applyPaymentResult($order, $actualPrice, $tradeNo);
        $completedOrder = $this->orderFulfillmentService->fulfill($order);
        $this->goodsService->salesVolumeIncr($completedOrder->goods_id, $completedOrder->buy_amount);

        return $completedOrder;
    }

    /**
     * 验证可完成支付的订单
     *
     * @param string $orderSN
     * @param float $actualPrice
     * @return Order
     * @throws \Exception
     */
    public function validateCompletableOrder(string $orderSN, float $actualPrice): Order
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order) {
            throw new \Exception(__('dujiaoka.prompt.order_does_not_exist'));
        }
        if ($order->status == Order::STATUS_COMPLETED) {
            throw new \Exception(__('dujiaoka.prompt.order_status_completed'));
        }
        if (bccomp($order->actual_price, $actualPrice, 2) != 0) {
            throw new \Exception(__('dujiaoka.prompt.order_inconsistent_amounts'));
        }

        return $order;
    }

    /**
     * 应用支付结果
     *
     * @param Order $order
     * @param float $actualPrice
     * @param string $tradeNo
     */
    public function applyPaymentResult(Order $order, float $actualPrice, string $tradeNo): void
    {
        $order->actual_price = $actualPrice;
        $order->trade_no = $tradeNo;
    }
}
