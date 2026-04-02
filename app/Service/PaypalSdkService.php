<?php

namespace App\Service;

use App\Exceptions\PaymentGatewayException;
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
    /**
     * @var \App\Service\PaypalCallbackUrlService
     */
    private $paypalCallbackUrlService;

    public function __construct()
    {
        $this->paypalCallbackUrlService = app(PaypalCallbackUrlService::class);
    }

    public function createApprovalLink(Order $order, Pay $payGateway, float $total): string
    {
        try {
            $paypal = $this->makeApiContext($payGateway);
            $payment = $this->makePayment($order, $total);
            $payment->create($paypal);

            return $payment->getApprovalLink();
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'PayPal approval link creation failed.');
        }
    }

    public function executeApprovedPayment(Pay $payGateway, string $paymentId, string $payerId): void
    {
        try {
            $paypal = $this->makeApiContext($payGateway);
            $payment = $this->loadPayment($paymentId, $paypal);
            $payment->execute($this->makePaymentExecution($payerId), $paypal);
        } catch (\Throwable $exception) {
            throw PaymentGatewayException::wrap($exception, 'PayPal approved payment execution failed.');
        }
    }

    protected function makeApiContext(Pay $payGateway): ApiContext
    {
        $paypal = new ApiContext(
            new OAuthTokenCredential(
                $payGateway->merchant_key,
                $payGateway->merchant_pem
            )
        );
        $paypal->setConfig(['mode' => config('dujiaoka.paypal_mode', 'live')]);

        return $paypal;
    }

    protected function loadPayment(string $paymentId, ApiContext $paypal): Payment
    {
        return Payment::get($paymentId, $paypal);
    }

    protected function makePayment(Order $order, float $total): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($order->title)
            ->setCurrency($this->getTargetCurrency())
            ->setQuantity(1)
            ->setPrice($total);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping(0)
            ->setSubtotal($total);

        $amount = new Amount();
        $amount->setCurrency($this->getTargetCurrency())
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($order->title)
            ->setInvoiceNumber($order->order_sn);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->paypalCallbackUrlService->successUrl($order))
            ->setCancelUrl($this->paypalCallbackUrlService->cancelUrl($order));

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

    protected function getTargetCurrency(): string
    {
        return (string) config('dujiaoka.paypal_target_currency', 'USD');
    }
}
