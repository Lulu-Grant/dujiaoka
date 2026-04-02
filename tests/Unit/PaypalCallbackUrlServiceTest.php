<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Service\PaypalCallbackUrlService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaypalCallbackUrlServiceTest extends TestCase
{
    public function test_it_builds_success_and_cancel_urls_from_route_contract(): void
    {
        Route::get('/pay/paypal/return_url', function () {
            return 'ok';
        })->name('paypal-return');

        $order = new Order();
        $order->order_sn = 'PAYPAL-CALLBACK-001';

        $service = app(PaypalCallbackUrlService::class);

        $this->assertStringContainsString('success=ok', $service->successUrl($order));
        $this->assertStringContainsString('orderSN=PAYPAL-CALLBACK-001', $service->successUrl($order));
        $this->assertStringContainsString('success=no', $service->cancelUrl($order));
        $this->assertStringContainsString('orderSN=PAYPAL-CALLBACK-001', $service->cancelUrl($order));
    }
}
