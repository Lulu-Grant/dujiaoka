<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;

class StripeCheckoutService
{
    /**
     * @var \App\Service\StripeCurrencyService
     */
    protected $stripeCurrencyService;

    /**
     * @var \App\Service\StripeRouteService
     */
    protected $stripeRouteService;

    public function __construct()
    {
        $this->stripeCurrencyService = app(StripeCurrencyService::class);
        $this->stripeRouteService = app(StripeRouteService::class);
    }

    public function buildCheckoutViewData(Order $order, Pay $payGateway): array
    {
        $targetAmount = $this->stripeCurrencyService->convertCnyToUsd((float) $order->actual_price);

        return [
            'amount_cny' => (float) bcmul($order->actual_price, 100, 2),
            'amount_usd' => (float) bcmul($targetAmount, 100, 2),
            'price' => (float) $order->actual_price,
            'orderid' => $order->order_sn,
            'publishable_key' => $payGateway->merchant_id,
            'return_url' => $this->stripeRouteService->returnUrl($order, $payGateway),
            'detail_url' => $this->stripeRouteService->detailUrl($order),
            'check_url' => $this->stripeRouteService->checkUrl(),
            'charge_url' => $this->stripeRouteService->chargeUrl(),
        ];
    }

    protected function getSourceCurrency(): string
    {
        return (string) config('dujiaoka.stripe_source_currency', 'CNY');
    }

    protected function getTargetCurrency(): string
    {
        return (string) config('dujiaoka.stripe_target_currency', 'USD');
    }
}
