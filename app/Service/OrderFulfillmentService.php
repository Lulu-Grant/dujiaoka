<?php

namespace App\Service;

use App\Models\Order;

class OrderFulfillmentService
{
    /**
     * @var \App\Service\CarmisService
     */
    private $carmisService;

    /**
     * @var \App\Service\GoodsService
     */
    private $goodsService;

    /**
     * @var \App\Service\OrderNotificationService
     */
    private $orderNotificationService;

    public function __construct()
    {
        $this->carmisService = app('Service\CarmisService');
        $this->goodsService = app('Service\GoodsService');
        $this->orderNotificationService = app(OrderNotificationService::class);
    }

    /**
     * 执行订单履约
     *
     * @param Order $order
     * @return Order
     */
    public function fulfill(Order $order): Order
    {
        if ($order->type == Order::AUTOMATIC_DELIVERY) {
            return $this->fulfillAutomatic($order);
        }

        return $this->fulfillManual($order);
    }

    /**
     * 手动处理订单
     *
     * @param Order $order
     * @return Order
     */
    public function fulfillManual(Order $order): Order
    {
        $order->status = Order::STATUS_PENDING;
        $order->save();
        $this->goodsService->inStockDecr($order->goods_id, $order->buy_amount);
        $this->orderNotificationService->sendManualManageMail($order);

        return $order;
    }

    /**
     * 自动发货订单
     *
     * @param Order $order
     * @return Order
     */
    public function fulfillAutomatic(Order $order): Order
    {
        $carmis = $this->carmisService->withGoodsByAmountAndStatusUnsold($order->goods_id, $order->buy_amount);
        if (count($carmis) != $order->buy_amount) {
            $order->info = __('dujiaoka.prompt.order_carmis_insufficient_quantity_available');
            $order->status = Order::STATUS_ABNORMAL;
            $order->save();

            return $order;
        }

        $carmisInfo = array_column($carmis, 'carmi');
        $ids = array_column($carmis, 'id');
        $order->info = implode(PHP_EOL, $carmisInfo);
        $order->status = Order::STATUS_COMPLETED;
        $order->save();
        $this->carmisService->soldByIDS($ids);
        $this->orderNotificationService->sendAutomaticDeliveryMail($order, implode('<br/>', $carmisInfo));

        return $order;
    }
}
