<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalReturnService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
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
     * 构建 paypal API context
     *
     * @param Pay $payGateway
     * @return ApiContext
     */
    public function makeApiContext(Pay $payGateway): ApiContext
    {
        $paypal = new ApiContext(
            new OAuthTokenCredential(
                $payGateway->merchant_key,
                $payGateway->merchant_pem
            )
        );
        $paypal->setConfig(['mode' => 'live']);

        return $paypal;
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

        $paypal = $this->makeApiContext($payGateway);
        $payment = $this->loadPayment($paymentId, $paypal);
        $execute = $this->makePaymentExecution($payerId);

        try {
            $this->executePayment($payment, $execute, $paypal);
            $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $paymentId);

            return 'success';
        } catch (\Exception $exception) {
            return 'fail';
        }
    }

    protected function loadPayment(string $paymentId, ApiContext $paypal): Payment
    {
        return Payment::get($paymentId, $paypal);
    }

    protected function makePaymentExecution(string $payerId): PaymentExecution
    {
        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        return $execute;
    }

    protected function executePayment(Payment $payment, PaymentExecution $execute, ApiContext $paypal): void
    {
        $payment->execute($execute, $paypal);
    }
}
