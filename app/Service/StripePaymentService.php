<?php

namespace App\Service;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\StripeGatewayClientInterface;

class StripePaymentService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    /**
     * @var \App\Service\Contracts\StripeGatewayClientInterface
     */
    private $stripeGatewayClient;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
        $this->stripeGatewayClient = app(StripeGatewayClientInterface::class);
    }

    public function handleReturn(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'redirect';
        }

        try {
            $this->stripeGatewayClient->setApiKey($payGateway->merchant_pem);
            $source = $this->stripeGatewayClient->retrieveSource($sourceId);
            if ($source->status == 'chargeable') {
                $this->stripeGatewayClient->createCharge([
                    'amount' => $source->amount,
                    'currency' => $source->currency,
                    'source' => $sourceId,
                ]);

                if (($source->owner->name ?? null) == $orderSN) {
                    $this->paymentCallbackService->completeOrder($orderSN, (float) $source->amount / 100, $source->id);
                }
            }
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'Stripe return handling failed.');
        }

        return 'redirect';
    }

    public function handleSourceCheck(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'fail';
        }

        try {
            $this->stripeGatewayClient->setApiKey($payGateway->merchant_pem);
            $source = $this->stripeGatewayClient->retrieveSource($sourceId);
            if ($source->status == 'chargeable') {
                $this->stripeGatewayClient->createCharge([
                    'amount' => $source->amount,
                    'currency' => $source->currency,
                    'source' => $sourceId,
                ]);
            }

            if ($source->status == 'consumed' && ($source->owner->name ?? null) == $orderSN) {
                $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $source->id);

                return 'success';
            }
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'Stripe source check failed.');
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
            $this->stripeGatewayClient->setApiKey($payGateway->merchant_pem);
            $result = $this->stripeGatewayClient->createCharge([
                'amount' => $usdAmount,
                'currency' => $this->getTargetCurrency(),
                'source' => $stripeToken,
            ]);
            if ($result->status == 'succeeded') {
                $this->paymentCallbackService->completeOrder($orderSN, (float) $order->actual_price, $stripeToken);

                return 'success';
            }

            return (string) $result->status;
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'Stripe card charge failed.');
        }
    }

    /**
     * @return array{0: Order|null,1: Pay|null}
     */
    protected function resolveStripeContext(string $orderSN): array
    {
        return $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/stripe');
    }

    protected function getTargetCurrency(): string
    {
        return strtolower((string) config('dujiaoka.stripe_target_currency', 'USD'));
    }
}
