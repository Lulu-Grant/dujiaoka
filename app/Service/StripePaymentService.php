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

    /**
     * @var \App\Service\StripeSdkService
     */
    private $stripeSdkService;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
        $this->stripeSdkService = app(StripeSdkService::class);
    }

    public function handleReturn(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'redirect';
        }

        $this->stripeSdkService->setApiKey($payGateway->merchant_pem);
        $source = $this->stripeSdkService->retrieveSource($sourceId);
        if ($source->status == 'chargeable') {
            $this->stripeSdkService->createCharge([
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

        $this->stripeSdkService->setApiKey($payGateway->merchant_pem);
        $source = $this->stripeSdkService->retrieveSource($sourceId);
        if ($source->status == 'chargeable') {
            $this->stripeSdkService->createCharge([
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
            $this->stripeSdkService->setApiKey($payGateway->merchant_pem);
            $result = $this->stripeSdkService->createCharge([
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
}
