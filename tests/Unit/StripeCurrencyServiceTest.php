<?php

namespace Tests\Unit;

use App\Service\StripeCurrencyService;
use Tests\TestCase;

class StripeCurrencyServiceTest extends TestCase
{
    public function test_convert_cny_to_usd_uses_rate_feed(): void
    {
        $service = new class extends StripeCurrencyService {
            protected function fetchRates(): array
            {
                return [
                    'body' => [
                        'data' => [
                            [
                                'ccyNbr' => '美元',
                                'rtcOfr' => '725.00',
                            ],
                        ],
                    ],
                ];
            }
        };

        $usd = $service->convertCnyToUsd(10.00);

        $this->assertSame(1.3, $usd);
    }
}
