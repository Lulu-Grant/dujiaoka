<?php

namespace App\Service\Contracts;

use App\Models\Order;
use App\Models\Pay;
use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;

interface PaypalGatewayClientInterface
{
    /**
     * @param Pay $payGateway
     */
    public function makeApiContext(Pay $payGateway): ApiContext;

    /**
     * @param Order $order
     * @param float $total
     */
    public function createApprovalLink(Order $order, float $total, ApiContext $paypal): string;

    /**
     * @param string $paymentId
     */
    public function loadPayment(string $paymentId, ApiContext $paypal): Payment;

    /**
     * @param string $payerId
     */
    public function executeApprovedPayment(Payment $payment, string $payerId, ApiContext $paypal): void;
}
