<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Pay;
use App\Service\StripeRouteService;
use Tests\TestCase;

class StripeRouteServiceTest extends TestCase
{
    public function test_it_builds_checkout_related_urls(): void
    {
        $order = new Order();
        $order->order_sn = 'STRIPE-ROUTE-001';

        $pay = new Pay();
        $pay->pay_handleroute = '/pay/stripe';

        $service = app(StripeRouteService::class);

        $this->assertStringContainsString('/pay/stripe/return_url/', $service->returnUrl($order, $pay));
        $this->assertStringContainsString('orderid=STRIPE-ROUTE-001', $service->returnUrl($order, $pay));
        $this->assertStringContainsString('detail-order-sn', $service->detailUrl($order));
        $this->assertStringContainsString('/pay/stripe/check', $service->checkUrl());
        $this->assertStringContainsString('/pay/stripe/charge', $service->chargeUrl());
    }
}
