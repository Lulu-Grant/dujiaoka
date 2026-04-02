<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;

class StripeRouteService
{
    public function returnUrl(Order $order, Pay $payGateway): string
    {
        return site_url() . $payGateway->pay_handleroute . '/return_url/?orderid=' . $order->order_sn;
    }

    public function detailUrl(Order $order): string
    {
        return url('detail-order-sn', ['orderSN' => $order->order_sn]);
    }

    public function checkUrl(): string
    {
        return url('/pay/stripe/check');
    }

    public function chargeUrl(): string
    {
        return url('/pay/stripe/charge');
    }
}
