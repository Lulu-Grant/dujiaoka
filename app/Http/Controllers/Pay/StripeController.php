<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\StripeCheckoutService;
use App\Service\StripeCurrencyService;
use App\Service\OrderService;
use App\Service\StripePaymentService;
use Illuminate\Http\Request;

class StripeController extends PayController
{
    /**
     * @var \App\Service\StripePaymentService
     */
    private $stripePaymentService;

    /**
     * @var \App\Service\OrderService
     */
    private $orderService;

    /**
     * @var \App\Service\StripeCheckoutService
     */
    private $stripeCheckoutService;

    /**
     * @var \App\Service\StripeCurrencyService
     */
    private $stripeCurrencyService;

    public function __construct()
    {
        parent::__construct();
        $this->stripePaymentService = app(StripePaymentService::class);
        $this->orderService = app(OrderService::class);
        $this->stripeCheckoutService = app(StripeCheckoutService::class);
        $this->stripeCurrencyService = app(StripeCurrencyService::class);
    }

    public function gateway(string $payway, string $orderSN)
    {


        // 加载网关
        $this->loadGateWay($orderSN, $payway);
        //构造要请求的参数数组，无需改动
        switch ($payway) {
            case 'wx':
            case 'alipay':
            default:
                try {
                    $viewData = $this->stripeCheckoutService->buildCheckoutViewData($this->order, $this->payGateway);

                    return view('stripe.checkout', $viewData);
                } catch (\Exception $e) {
                    throw new RuleValidationException(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                }
                break;
        }
    }

    public function returnUrl(Request $request)
    {

        $data = $request->all();
        $this->stripePaymentService->handleReturn($data['orderid'], $data['source']);
        return redirect(url('detail-order-sn', ['orderSN' => $data['orderid']]));
    }

    public function check(Request $request)
    {

        $data = $request->all();
        return $this->stripePaymentService->handleSourceCheck($data['orderid'], $data['source']);

    }

    public function charge(Request $request)
    {
        $data = $request->all();
        $cacheord = $this->orderService->detailOrderSN($data['orderid']);
        if (!$cacheord) {
            return 'fail';
        }

        $usdAmount = (float) bcmul($this->getUsdCurrency($cacheord->actual_price), 100, 0);

        return $this->stripePaymentService->handleCardCharge($data['orderid'], $data['stripeToken'], $usdAmount);
    }

    /**
     * 根据RMB获取美元
     * @param $cny
     * @return float|int
     * @throws \Exception
     */
    public function getUsdCurrency($cny)
    {
        return $this->stripeCurrencyService->convertCnyToUsd((float) $cny);
    }


}
