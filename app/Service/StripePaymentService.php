<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;

class StripePaymentService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
    }

    public function handleReturn(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'redirect';
        }

        $this->setApiKey($payGateway->merchant_pem);
        $source = $this->retrieveSource($sourceId);
        if ($source->status == 'chargeable') {
            $this->createCharge([
                'amount' => $source->amount,
                'currency' => $source->currency,
                'source' => $sourceId,
            ]);

            if (($source->owner->name ?? null) == $orderSN) {
                $this->paymentCallbackService->completeOrder($orderSN, (float) $source->amount / 100, $source->id);
            }
        }

        return 'redirect';
    }

    public function handleSourceCheck(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'fail';
        }

        $this->setApiKey($payGateway->merchant_pem);
        $source = $this->retrieveSource($sourceId);
        if ($source->status == 'chargeable') {
            $this->createCharge([
                'amount' => $source->amount,
                'currency' => $source->currency,
                'source' => $sourceId,
            ]);
        }

        if ($source->status == 'consumed' && ($source->owner->name ?? null) == $orderSN) {
            $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $source->id);

            return 'success';
        }

        return 'fail';
    }

    public function handleCardCharge(string $orderSN, string $stripeToken, float $usdAmount): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'fail';
        }

        try {
            $this->setApiKey($payGateway->merchant_pem);
            $result = $this->createCharge([
                'amount' => $usdAmount,
                'currency' => 'usd',
                'source' => $stripeToken,
            ]);
            if ($result->status == 'succeeded') {
                $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $stripeToken);

                return 'success';
            }

            return (string) $result->status;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return array{0: Order|null,1: Pay|null}
     */
    protected function resolveStripeContext(string $orderSN): array
    {
        return $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/stripe');
    }

    protected function setApiKey(string $apiKey): void
    {
        \Stripe\Stripe::setApiKey($apiKey);
    }

    protected function retrieveSource(string $sourceId)
    {
        return \Stripe\Source::retrieve($sourceId);
    }

    protected function createCharge(array $payload)
    {
        return \Stripe\Charge::create($payload);
    }
}
