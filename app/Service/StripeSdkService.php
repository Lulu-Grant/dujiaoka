<?php

namespace App\Service;

use App\Service\Contracts\StripeGatewayClientInterface;

class StripeSdkService implements StripeGatewayClientInterface
{
    public function setApiKey(string $apiKey): void
    {
        \Stripe\Stripe::setApiKey($apiKey);
    }

    public function retrieveSource(string $sourceId)
    {
        return \Stripe\Source::retrieve($sourceId);
    }

    public function createCharge(array $payload)
    {
        return \Stripe\Charge::create($payload);
    }
}
