<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;

class PaymentCallbackService
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
     * 解析支付回调上下文
     *
     * @param string $orderSN
     * @param string $expectedHandlerRoute
     * @return array{0: Order|null, 1: Pay|null}
     */
    public function resolveCallbackContext(string $orderSN, string $expectedHandlerRoute): array
    {
        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order) {
            return [null, null];
        }

        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            return [null, null];
        }

        if ($payGateway->pay_handleroute != $expectedHandlerRoute) {
            return [null, null];
        }

        return [$order, $payGateway];
    }

    /**
     * 执行支付完成
     *
     * @param string $orderSN
     * @param float $amount
     * @param string $tradeNo
     * @return Order
     */
    public function completeOrder(string $orderSN, float $amount, string $tradeNo)
    {
        return $this->orderProcessService->completedOrder($orderSN, $amount, $tradeNo);
    }

    /**
     * 处理带签名校验的支付通知
     *
     * @param string $orderSN
     * @param string $expectedHandlerRoute
     * @param callable $signatureValidator
     * @param float $amount
     * @param string $tradeNo
     * @param string $missingContextResponse
     * @param string $invalidSignatureResponse
     * @param string $successResponse
     * @return string
     */
    public function handleSignedNotification(
        string $orderSN,
        string $expectedHandlerRoute,
        callable $signatureValidator,
        float $amount,
        string $tradeNo,
        string $missingContextResponse = 'fail',
        string $invalidSignatureResponse = 'fail',
        string $successResponse = 'success'
    ): string {
        [$order, $payGateway] = $this->resolveCallbackContext($orderSN, $expectedHandlerRoute);
        if (!$order || !$payGateway) {
            return $missingContextResponse;
        }

        if (!$signatureValidator($order, $payGateway)) {
            return $invalidSignatureResponse;
        }

        $this->completeOrder($orderSN, $amount, $tradeNo);

        return $successResponse;
    }
}
