<?php
/**
 * VpayController.php
 * V免签
 * Author iLay1678
 * Created on 2020/5/1 11:59
 */

namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\PaymentCallbackService;
use Illuminate\Http\Request;

class VpayController extends PayController
{
    /**
     * @var \App\Service\PaymentCallbackService
     */
    private $paymentCallbackService;

    public function __construct()
    {
        parent::__construct();
        $this->paymentCallbackService = app(PaymentCallbackService::class);
    }


    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "payId" => date('YmdHis') . rand(1, 65535),//平台ID号
                "price" => (float)$this->order->actual_price,//原价
                'param' => $this->order->order_sn,
                'returnUrl' => route('vpay-return', ['order_id' => $this->order->order_sn]),
                'notifyUrl' => url($this->payGateway->pay_handleroute . '/notify_url'),
                "isHtml" => 1,
            );
            switch ($payway) {
                case 'vzfb':
                    $parameter['type'] = 2;
                    break;
                case 'vwx':
                default:
                    $parameter['type'] = 1;
                    break;
            }
            $parameter['sign'] = md5($parameter['payId'] . $parameter['param'] . $parameter['type'] . $parameter['price'] . $this->payGateway->merchant_id);
            $payurl = $this->payGateway->merchant_pem . 'createOrder?' . http_build_query($parameter); //支付页面
            return redirect()->away($payurl);
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }


    public function notifyUrl(Request $request)
    {
        $data = $request->all();

        return $this->paymentCallbackService->handleSignedNotification(
            $data['param'],
            '/pay/vpay',
            function ($order, $payGateway) use ($data) {
                $key = $payGateway->merchant_id;
                $payId = $data['payId'];
                $param = $data['param'];
                $type = $data['type'];
                $price = $data['price'];
                $reallyPrice = $data['reallyPrice'];
                $sign = $data['sign'];
                $_sign = md5($payId . $param . $type . $price . $reallyPrice . $key);

                return $_sign == $sign;
            },
            (float) $data['price'],
            $data['payId'],
            'fail',
            'fail',
            'success'
        );
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('order_id');
        // 异步通知还没到就跳转了，所以这里休眠2秒
        sleep(2);
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }

}
