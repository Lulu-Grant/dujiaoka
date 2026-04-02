<?php

namespace App\Service;

use Xhat\Payjs\Facades\Payjs;

class PayjsNotificationService
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
        [$order, $payGateway] = $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/payjs');
        if (!$order || !$payGateway) {
            return 'error';
        }

        config([
            'payjs.mchid' => $payGateway->merchant_id,
            'payjs.key' => $payGateway->merchant_pem,
        ]);

        $notifyInfo = $this->getNotifyInfo();
        $totalFee = bcdiv($notifyInfo['total_fee'], 100, 2);
        $this->paymentCallbackService->completeOrder(
            $notifyInfo['out_trade_no'],
            (float) $totalFee,
            $notifyInfo['payjs_order_id']
        );

        return 'success';
    }

    protected function getNotifyInfo(): array
    {
        return Payjs::notify();
    }
}
