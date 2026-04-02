<?php

namespace App\Service;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\PaypalGatewayClientInterface;

class PaypalCheckoutService
{
    const CURRENCY = 'USD';

    /**
     * @var \App\Service\Contracts\PaypalGatewayClientInterface
     */
    private $paypalGatewayClient;

    public function __construct()
    {
        $this->paypalGatewayClient = app(PaypalGatewayClientInterface::class);
    }

    /**
     * 创建 Paypal 支付链接
     *
     * @param Order $order
     * @param Pay $payGateway
     * @return string
     */
    public function createApprovalUrl(Order $order, Pay $payGateway): string
    {
        $total = $this->convertAmount((float) $order->actual_price);
        return $this->paypalGatewayClient->createApprovalLink($order, $payGateway, $total);
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
