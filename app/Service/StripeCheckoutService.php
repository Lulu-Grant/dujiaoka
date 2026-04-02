<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;

class StripeCheckoutService
{
    /**
     * @var \App\Service\StripeAmountService
     */
    protected $stripeAmountService;

    /**
     * @var \App\Service\StripeRouteService
     */
    protected $stripeRouteService;

    public function __construct()
    {
        $this->stripeAmountService = app(StripeAmountService::class);
        $this->stripeRouteService = app(StripeRouteService::class);
    }

    public function buildCheckoutViewData(Order $order, Pay $payGateway): array
    {
        return [
            'amount_cny' => $this->stripeAmountService->sourceMinorUnits((float) $order->actual_price),
            'amount_usd' => $this->stripeAmountService->targetMinorUnits((float) $order->actual_price),
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
        return $this->stripeAmountService->sourceCurrency();
    }

    protected function getTargetCurrency(): string
    {
        return strtoupper($this->stripeAmountService->targetCurrency());
    }
}
