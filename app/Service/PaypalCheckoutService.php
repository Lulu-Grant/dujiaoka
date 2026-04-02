<?php

namespace App\Service;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Models\Order;
use App\Models\Pay;
use PayPal\Exception\PayPalConnectionException;

class PaypalCheckoutService
{
    const CURRENCY = 'USD';

    /**
     * @var \App\Service\PaypalSdkService
     */
    private $paypalSdkService;

    public function __construct()
    {
        $this->paypalSdkService = app(PaypalSdkService::class);
    }

    /**
     * 创建 Paypal 支付链接
     *
     * @param Order $order
     * @param Pay $payGateway
     * @return string
     * @throws PayPalConnectionException
     */
    public function createApprovalUrl(Order $order, Pay $payGateway): string
    {
        $paypal = $this->paypalSdkService->makeApiContext($payGateway);
        $total = $this->convertAmount((float) $order->actual_price);
        return $this->paypalSdkService->createApprovalLink($order, $total, $paypal);
    }

    protected function convertAmount(float $amount): float
    {
        return (float) Currency::convert()
            ->from('CNY')
            ->to(self::CURRENCY)
            ->amount($amount)
            ->round(2)
            ->get();
    }
}
