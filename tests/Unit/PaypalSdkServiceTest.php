<?php

namespace Tests\Unit;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Pay;
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
}
