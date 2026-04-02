<?php

namespace App\Service;

use Yansongda\Pay\Pay;

class WepayNotificationService
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
        [$order, $payGateway] = $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/wepay');
        if (!$order || !$payGateway) {
            return 'error';
        }

        $pay = $this->buildPayClient($payGateway->merchant_id, $payGateway->merchant_key, $payGateway->merchant_pem);

        try {
            $result = $this->verifyNotification($pay);
            $totalFee = bcdiv($result->total_fee, 100, 2);
            $this->paymentCallbackService->completeOrder(
                $result->out_trade_no,
                (float) $totalFee,
                $result->transaction_id
            );

            return 'success';
        } catch (\Exception $exception) {
            return 'fail';
        }
    }

    protected function buildPayClient(string $appId, string $mchId, string $key)
    {
        return Pay::wechat([
            'app_id' => $appId,
            'mch_id' => $mchId,
            'key' => $key,
        ]);
    }

    protected function verifyNotification($pay)
    {
        return $pay->verify();
    }
}
