<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\PaymentGatewayException;
use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\StripeAmountService;
use App\Service\DataTransferObjects\StripeRequestData;
use App\Service\StripeCheckoutService;
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
     * @var \App\Service\StripeAmountService
     */
    private $stripeAmountService;

    public function __construct()
    {
        parent::__construct();
        $this->stripePaymentService = app(StripePaymentService::class);
        $this->orderService = app(OrderService::class);
        $this->stripeCheckoutService = app(StripeCheckoutService::class);
        $this->stripeAmountService = app(StripeAmountService::class);
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
        $data = StripeRequestData::fromRequest($request);
        try {
            $this->stripePaymentService->handleReturn($data->orderSN, (string) $data->sourceId);
        } catch (PaymentGatewayException $exception) {
            return $this->err($exception->getMessage());
        }
        return redirect(url('detail-order-sn', ['orderSN' => $data->orderSN]));
    }

    public function check(Request $request)
    {
        $data = StripeRequestData::fromRequest($request);
        try {
            return $this->stripePaymentService->handleSourceCheck($data->orderSN, (string) $data->sourceId);
        } catch (PaymentGatewayException $exception) {
            return $exception->getMessage();
        }

    }

    public function charge(Request $request)
    {
        $data = StripeRequestData::fromRequest($request);
        $cacheord = $this->orderService->detailOrderSN($data->orderSN);
        if (!$cacheord) {
            return 'fail';
        }

        $usdAmount = $this->stripeAmountService->targetMinorUnits((float) $cacheord->actual_price);

        try {
            return $this->stripePaymentService->handleCardCharge($data->orderSN, (string) $data->stripeToken, $usdAmount);
        } catch (PaymentGatewayException $exception) {
            return $exception->getMessage();
        }
    }
}
