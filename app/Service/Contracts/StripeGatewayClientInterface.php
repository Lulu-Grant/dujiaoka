<?php

namespace App\Service\Contracts;

interface StripeGatewayClientInterface
{
    public function setApiKey(string $apiKey): void;

    /**
     * @param string $sourceId
     * @return mixed
     */
    public function retrieveSource(string $sourceId);

    /**
     * @param array<string, mixed> $payload
     * @return mixed
     */
    public function createCharge(array $payload);
}
