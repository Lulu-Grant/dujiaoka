<?php

namespace App\Service;

use Yansongda\Pay\Pay;

class AlipayNotificationService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
    }

    public function handleNotification(string $orderSN): string
    {
        [$order, $payGateway] = $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/alipay');
        if (!$order || !$payGateway) {
            return 'error';
        }

        $pay = $this->buildPayClient($payGateway->merchant_id, $payGateway->merchant_key, $payGateway->merchant_pem);

        try {
            $result = $this->verifyNotification($pay);
            if ($result->trade_status == 'TRADE_SUCCESS' || $result->trade_status == 'TRADE_FINISHED') {
                $this->paymentCallbackService->completeOrder(
                    $result->out_trade_no,
                    (float) $result->total_amount,
                    $result->trade_no
                );
            }

            return 'success';
        } catch (\Exception $exception) {
            return 'fail';
        }
    }

    protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
    {
        return Pay::alipay([
            'app_id' => $appId,
            'ali_public_key' => $publicKey,
            'private_key' => $privateKey,
        ]);
    }

    protected function verifyNotification($pay)
    {
        return $pay->verify();
    }
}
