<?php

namespace App\Service;

class StripeAmountService
{
    /**
     * @var \App\Service\StripeCurrencyService
     */
    protected $stripeCurrencyService;

    public function __construct()
    {
        $this->stripeCurrencyService = app(StripeCurrencyService::class);
    }

    public function convertSourceToTarget(float $amount): float
    {
        return $this->stripeCurrencyService->convertCnyToUsd($amount);
    }

    public function sourceMinorUnits(float $amount): float
    {
        return (float) bcmul($amount, 100, 2);
    }

    public function targetMinorUnits(float $amount): float
    {
        return (float) bcmul($this->convertSourceToTarget($amount), 100, 2);
    }

    public function targetCurrency(): string
    {
        return strtolower((string) config('dujiaoka.stripe_target_currency', 'USD'));
    }

    public function sourceCurrency(): string
    {
        return (string) config('dujiaoka.stripe_source_currency', 'CNY');
    }
}
