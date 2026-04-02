<?php

namespace App\Service;

class CoinbaseWebhookService
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        $this->paymentCallbackService = app(PaymentCallbackService::class);
    }

    public function handleWebhook(string $payload, string $signature): string
    {
        $data = json_decode($payload, true);
        $eventData = $data['event']['data'] ?? null;
        $orderSN = $eventData['metadata']['customer_id'] ?? '';

        if (!$eventData || $orderSN === '') {
            return 'fail';
        }

        [$order, $payGateway] = $this->paymentCallbackService->resolveCallbackContext($orderSN, '/pay/coinbase');
        if (!$order || !$payGateway) {
            return 'fail';
        }

        $expectedSignature = hash_hmac('sha256', $payload, $payGateway->merchant_pem);
        if ($signature !== $expectedSignature) {
            return 'fail|wrong sig';
        }

        $confirmedPayment = $this->findConfirmedPayment($eventData['payments'] ?? []);
        if (!$confirmedPayment) {
            return 'fail';
        }

        $currency = $confirmedPayment['value']['local']['currency'] ?? '';
        if ($currency !== 'CNY') {
            return 'error|Notify: Wrong currency:' . $currency;
        }

        $paidAmount = (float) ($confirmedPayment['value']['local']['amount'] ?? 0);
        if (bccomp((string) $order->actual_price, (string) $paidAmount, 2) === 1) {
            throw new \Exception(__('Coinbase付款金额不足'));
        }

        $tradeNo = (string) ($eventData['code'] ?? '');
        $this->paymentCallbackService->completeOrder($order->order_sn, (float) $order->actual_price, $tradeNo);

        return '{"status": 200}';
    }

    private function findConfirmedPayment(array $payments): ?array
    {
        foreach ($payments as $payment) {
            $status = strtolower($payment['status'] ?? '');
            if (in_array($status, ['confirmed', 'resolved'], true)) {
                return $payment;
            }
        }

        return null;
    }
}
