<?php

namespace App\Service;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Models\Order;
use App\Models\Pay;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

class PaypalCheckoutService
{
    const CURRENCY = 'USD';

    /**
     * @var \App\Service\PaypalReturnService
     */
    private $paypalReturnService;

    public function __construct()
    {
        $this->paypalReturnService = app(PaypalReturnService::class);
    }

    /**
     * 创建 Paypal 支付链接
     *
     * @param Order $order
     * @param Pay $payGateway
     * @return string
     * @throws PayPalConnectionException
     */
    public function createApprovalUrl(Order $order, Pay $payGateway): string
    {
        $paypal = $this->paypalReturnService->makeApiContext($payGateway);
        $total = $this->convertAmount((float) $order->actual_price);
        $payment = $this->makePayment($order, $total);
        $payment->create($paypal);

        return $payment->getApprovalLink();
    }

    protected function convertAmount(float $amount): float
    {
        return (float) Currency::convert()
            ->from('CNY')
            ->to(self::CURRENCY)
            ->amount($amount)
            ->round(2)
            ->get();
    }

    protected function makePayment(Order $order, float $total): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($order->title)
            ->setCurrency(self::CURRENCY)
            ->setQuantity(1)
            ->setPrice($total);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping(0)
            ->setSubtotal($total);

        $amount = new Amount();
        $amount->setCurrency(self::CURRENCY)
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
}
