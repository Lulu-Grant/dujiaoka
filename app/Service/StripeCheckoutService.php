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

    public function __construct()
    {
        $this->stripeCurrencyService = app(StripeCurrencyService::class);
    }

    public function buildCheckoutViewData(Order $order, Pay $payGateway): array
    {
        $usdAmount = $this->stripeCurrencyService->convertCnyToUsd((float) $order->actual_price);

        return [
            'amount_cny' => (float) bcmul($order->actual_price, 100, 2),
            'amount_usd' => (float) bcmul($usdAmount, 100, 2),
            'price' => (float) $order->actual_price,
            'orderid' => $order->order_sn,
            'publishable_key' => $payGateway->merchant_id,
            'return_url' => site_url() . $payGateway->pay_handleroute . '/return_url/?orderid=' . $order->order_sn,
            'detail_url' => url('detail-order-sn', ['orderSN' => $order->order_sn]),
            'check_url' => url('/pay/stripe/check'),
            'charge_url' => url('/pay/stripe/charge'),
        ];
    }
}
