<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\PaypalGatewayClientInterface;

class PaypalReturnService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    /**
     * @var \App\Service\Contracts\PaypalGatewayClientInterface
     */
    private $paypalGatewayClient;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
        $this->paypalGatewayClient = app(PaypalGatewayClientInterface::class);
    }

    /**
     * 校验 paypal 回调对应的订单和网关
     *
     * @param string $orderSN
     * @return array{0: Order, 1: Pay}
     */
    public function resolveReturnContext(string $orderSN): array
    {
        return $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/paypal');
    }

    /**
     * 处理 paypal 同步返回并完成订单
     *
     * @param string $orderSN
     * @param string $paymentId
     * @param string $payerId
     * @return string
     */
    public function handleApprovedReturn(string $orderSN, string $paymentId, string $payerId): string
    {
        [$order, $payGateway] = $this->resolveReturnContext($orderSN);
        if (!$order || !$payGateway) {
            return 'error';
        }

        try {
            $this->paypalGatewayClient->executeApprovedPayment($payGateway, $paymentId, $payerId);
            $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $paymentId);

            return 'success';
        } catch (\Exception $exception) {
            return 'fail';
        }
    }
}
