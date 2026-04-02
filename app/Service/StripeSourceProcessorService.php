<?php

namespace App\Service;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\StripeGatewayClientInterface;

class StripeSourceProcessorService
{
    /**
     * @var \App\Service\Contracts\StripeGatewayClientInterface
     */
    private $stripeGatewayClient;

    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        $this->stripeGatewayClient = app(StripeGatewayClientInterface::class);
        $this->paymentCallbackService = app(PaymentCallbackService::class);
    }

    public function processReturn(Order $order, Pay $payGateway, string $sourceId): void
    {
        try {
            $this->configureGateway($payGateway);
            $source = $this->stripeGatewayClient->retrieveSource($sourceId);
            $this->chargeSourceIfNeeded($sourceId, $source);

            if ($this->sourceBelongsToOrder($source, $order->order_sn)) {
                $this->paymentCallbackService->completeOrder(
                    $order->order_sn,
                    (float) $source->amount / 100,
                    $source->id
                );
            }
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'Stripe return handling failed.');
        }
    }

    public function processSourceCheck(Order $order, Pay $payGateway, string $sourceId): string
    {
        try {
            $this->configureGateway($payGateway);
            $source = $this->stripeGatewayClient->retrieveSource($sourceId);
            $this->chargeSourceIfNeeded($sourceId, $source);

            if ($this->sourceIsConsumedForOrder($source, $order->order_sn)) {
                $this->paymentCallbackService->completeOrder(
                    $order->order_sn,
                    (float) $order->actual_price,
                    $source->id
                );

                return 'success';
            }

            return 'fail';
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'Stripe source check failed.');
        }
    }

    protected function configureGateway(Pay $payGateway): void
    {
        $this->stripeGatewayClient->setApiKey($payGateway->merchant_pem);
    }

    /**
     * @param object $source
     */
    protected function chargeSourceIfNeeded(string $sourceId, $source): void
    {
        if (($source->status ?? null) !== 'chargeable') {
            return;
        }

        $this->stripeGatewayClient->createCharge([
            'amount' => $source->amount,
            'currency' => $source->currency,
            'source' => $sourceId,
        ]);
    }

    /**
     * @param object $source
     */
    protected function sourceBelongsToOrder($source, string $orderSN): bool
    {
        return ($source->owner->name ?? null) === $orderSN;
    }

    /**
     * @param object $source
     */
    protected function sourceIsConsumedForOrder($source, string $orderSN): bool
    {
        return ($source->status ?? null) === 'consumed' && $this->sourceBelongsToOrder($source, $orderSN);
    }
}
