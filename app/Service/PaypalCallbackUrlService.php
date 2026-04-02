<?php

namespace App\Service;

use App\Models\Order;

class PaypalCallbackUrlService
{
    public function successUrl(Order $order): string
    {
        return $this->buildUrl('ok', $order);
    }

    public function cancelUrl(Order $order): string
    {
        return $this->buildUrl('no', $order);
    }

    protected function buildUrl(string $success, Order $order): string
    {
        $parameters = [
            'success' => $success,
            'orderSN' => $order->order_sn,
        ];

        $routes = app('router')->getRoutes();
        if ($routes && $routes->hasNamedRoute('paypal-return')) {
            return route('paypal-return', $parameters);
        }

        return url('pay/paypal/return_url') . '?' . http_build_query($parameters);
    }
}
