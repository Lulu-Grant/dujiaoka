<?php

namespace Tests\Unit;

use App\Service\StripeAmountService;
use Tests\TestCase;

class StripeAmountServiceTest extends TestCase
{
    public function test_amount_service_reads_configured_currencies(): void
    {
        config([
            'dujiaoka.stripe_source_currency' => 'SGD',
            'dujiaoka.stripe_target_currency' => 'EUR',
        ]);

        $service = app(StripeAmountService::class);

        $this->assertSame('SGD', $service->sourceCurrency());
        $this->assertSame('eur', $service->targetCurrency());
    }
}
