<?php

namespace App\Service;

use App\Models\Order;
use App\Models\Pay;
use App\Service\Contracts\PaypalGatewayClientInterface;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalSdkService implements PaypalGatewayClientInterface
{
    public function makeApiContext(Pay $payGateway): ApiContext
    {
        $paypal = new ApiContext(
            new OAuthTokenCredential(
                $payGateway->merchant_key,
                $payGateway->merchant_pem
            )
        );
        $paypal->setConfig(['mode' => 'live']);

        return $paypal;
    }

    public function createApprovalLink(Order $order, float $total, ApiContext $paypal): string
    {
        $payment = $this->makePayment($order, $total);
        $payment->create($paypal);

        return $payment->getApprovalLink();
    }

    public function loadPayment(string $paymentId, ApiContext $paypal): Payment
    {
        return Payment::get($paymentId, $paypal);
    }

    public function executeApprovedPayment(Payment $payment, string $payerId, ApiContext $paypal): void
    {
        $payment->execute($this->makePaymentExecution($payerId), $paypal);
    }

    protected function makePayment(Order $order, float $total): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($order->title)
            ->setCurrency(PaypalCheckoutService::CURRENCY)
            ->setQuantity(1)
            ->setPrice($total);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping(0)
            ->setSubtotal($total);

        $amount = new Amount();
        $amount->setCurrency(PaypalCheckoutService::CURRENCY)
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($order->title)
            ->setInvoiceNumber($order->order_sn);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('paypal-return', ['success' => 'ok', 'orderSN' => $order->order_sn]))
            ->setCancelUrl(route('paypal-return', ['success' => 'no', 'orderSN' => $order->order_sn]));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        return $payment;
    }

    protected function makePaymentExecution(string $payerId): PaymentExecution
    {
        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        return $execute;
    }
}
