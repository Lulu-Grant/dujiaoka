<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\PaypalCheckoutService;
use App\Service\PaypalReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayPal\Exception\PayPalConnectionException;

class PaypalPayController extends PayController
{
    /**
     * @var \App\Service\PaypalReturnService
     */
    private $paypalReturnService;

    /**
     * @var \App\Service\PaypalCheckoutService
     */
    private $paypalCheckoutService;

    public function __construct()
    {
        parent::__construct();
        $this->paypalReturnService = app(PaypalReturnService::class);
        $this->paypalCheckoutService = app(PaypalCheckoutService::class);
    }

    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            $approvalUrl = $this->paypalCheckoutService->createApprovalUrl($this->order, $this->payGateway);
            return redirect($approvalUrl);
        } catch (PayPalConnectionException $payPalConnectionException) {
            return $this->err($payPalConnectionException->getMessage());
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    /**
     *paypal 同步回调
     */
    public function returnUrl(Request $request)
    {
        $success = $request->input('success');
        $paymentId =  $request->input('paymentId');
        $payerID =  $request->input('PayerID');
        $orderSN = $request->input('orderSN');
        if ($success == 'no' || empty($paymentId) || empty($payerID)) {
            // 取消支付
            return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));
        }

        $result = $this->paypalReturnService->handleApprovedReturn($orderSN, $paymentId, $payerID);
        if ($result === 'success') {
            Log::info("paypal支付成功",  ['支付成功，支付ID【' . $paymentId . '】,支付人ID【' . $payerID . '】']);
        } elseif ($result === 'fail') {
            Log::error("paypal支付失败", ['支付失败，支付ID【' . $paymentId . '】,支付人ID【' . $payerID . '】']);
        } elseif ($result === 'error') {
            return 'error';
        }

        return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));
    }


    /**
     * 异步通知
     * TODO: 暂未实现，但是好像只实现同步回调即可。这个可以放在后面实现
     */
    public function notifyUrl(Request $request)
    {
        //获取回调结果
        $json_data = $this->get_JsonData();
        if(!empty($json_data)){
            Log::debug("paypal notify info:\r\n" . json_encode($json_data));
        }else{
            Log::debug("paypal notify fail:参加为空");
        }

    }

    private function get_JsonData()
    {
        $json = file_get_contents('php://input');
        if ($json) {
            $json = str_replace("'", '', $json);
            $json = json_decode($json,true);
        }
        return $json;
    }

}
