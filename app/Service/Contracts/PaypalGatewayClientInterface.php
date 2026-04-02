<?php

namespace App\Service\Contracts;

use App\Models\Order;
use App\Models\Pay;

interface PaypalGatewayClientInterface
{
    /**
     * @param Order $order
     * @param Pay $payGateway
     * @param float $total
     */
    public function createApprovalLink(Order $order, Pay $payGateway, float $total): string;

    /**
     * @param string $payerId
     */
    public function executeApprovedPayment(Pay $payGateway, string $paymentId, string $payerId): void;
}
