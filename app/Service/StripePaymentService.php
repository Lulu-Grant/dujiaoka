<?php

namespace App\Service;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\StripeGatewayClientInterface;

class StripePaymentService
{
    /**
     * @var \App\Service\StripeSourceProcessorService
     */
    private $stripeSourceProcessorService;

    /**
     * @var \App\Service\Contracts\StripeGatewayClientInterface
     */
    private $stripeGatewayClient;

    public function __construct()
    {
        $this->stripeSourceProcessorService = app(StripeSourceProcessorService::class);
        $this->stripeGatewayClient = app(StripeGatewayClientInterface::class);
    }

    public function handleReturn(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'redirect';
        }

        $this->stripeSourceProcessorService->processReturn($order, $payGateway, $sourceId);

        return 'redirect';
    }

    public function handleSourceCheck(string $orderSN, string $sourceId): string
    {
        [$order, $payGateway] = $this->resolveStripeContext($orderSN);
        if (!$order || !$payGateway) {
            return 'fail';
        }

        return $this->stripeSourceProcessorService->processSourceCheck($order, $payGateway, $sourceId);
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
                app(PaymentCallbackService::class)->completeOrder($orderSN, (float) $order->actual_price, $stripeToken);

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
        return app(PaymentCallbackService::class)->resolveCallbackContext($orderSN, '/pay/stripe');
    }

    protected function getTargetCurrency(): string
    {
        return strtolower((string) config('dujiaoka.stripe_target_currency', 'USD'));
    }
}
