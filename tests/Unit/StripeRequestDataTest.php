<?php

namespace Tests\Unit;

use App\Service\DataTransferObjects\StripeRequestData;
use Illuminate\Http\Request;
use Tests\TestCase;

class StripeRequestDataTest extends TestCase
{
    public function test_it_builds_from_request(): void
    {
        $request = Request::create('/pay/stripe/charge', 'GET', [
            'orderid' => 'STRIPE-DTO-001',
            'source' => 'src_001',
            'stripeToken' => 'tok_001',
        ]);

        $data = StripeRequestData::fromRequest($request);

        $this->assertSame('STRIPE-DTO-001', $data->orderSN);
        $this->assertSame('src_001', $data->sourceId);
        $this->assertSame('tok_001', $data->stripeToken);
    }
}
