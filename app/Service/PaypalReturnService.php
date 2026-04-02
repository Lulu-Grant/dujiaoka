<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;
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
}
