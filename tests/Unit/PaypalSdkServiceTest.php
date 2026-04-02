<?php

namespace Tests\Unit;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Pay;
use App\Service\PaypalCallbackUrlService;
use App\Service\PaypalSdkService;
use Tests\TestCase;

class PaypalSdkServiceTest extends TestCase
{
    public function test_create_approval_link_wraps_sdk_errors(): void
    {
        $service = new class extends PaypalSdkService {
            protected function makeApiContext(Pay $payGateway): \PayPal\Rest\ApiContext
            {
                throw new \RuntimeException('sdk exploded');
            }
        };

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('PayPal approval link creation failed.');

        $service->createApprovalLink(new Order(), new Pay(), 1.23);
    }

    public function test_execute_approved_payment_wraps_sdk_errors(): void
    {
        $service = new class extends PaypalSdkService {
            protected function makeApiContext(Pay $payGateway): \PayPal\Rest\ApiContext
            {
                throw new \RuntimeException('sdk exploded');
            }
        };

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('PayPal approved payment execution failed.');

        $service->executeApprovedPayment(new Pay(), 'PAY-ID', 'PAYER-ID');
    }

    public function test_sdk_service_reads_configured_target_currency(): void
    {
        config(['dujiaoka.paypal_target_currency' => 'EUR']);

        $service = new class extends PaypalSdkService {
            public function targetCurrency(): string
            {
                return $this->getTargetCurrency();
            }
        };

        $this->assertSame('EUR', $service->targetCurrency());
    }

    public function test_sdk_service_uses_callback_url_service_contract(): void
    {
        $callbackUrlService = \Mockery::mock(PaypalCallbackUrlService::class);
        $callbackUrlService->shouldReceive('successUrl')->once()->andReturn('https://example.com/paypal/success');
        $callbackUrlService->shouldReceive('cancelUrl')->once()->andReturn('https://example.com/paypal/cancel');
        app()->instance(PaypalCallbackUrlService::class, $callbackUrlService);

        $service = new class extends PaypalSdkService {
            public function redirectUrlsFor(Order $order): array
            {
                $payment = $this->makePayment($order, 1.23);
                $redirectUrls = $payment->getRedirectUrls();

                return [$redirectUrls->getReturnUrl(), $redirectUrls->getCancelUrl()];
            }
        };

        $order = new Order();
        $order->order_sn = 'PAYPAL-CALLBACK-002';
        $order->title = 'PayPal Callback Product';

        $this->assertSame(
            ['https://example.com/paypal/success', 'https://example.com/paypal/cancel'],
            $service->redirectUrlsFor($order)
        );
    }
}
