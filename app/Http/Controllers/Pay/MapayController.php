<?php
namespace App\Http\Controllers\Pay;


use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\PaymentCallbackService;
use Illuminate\Http\Request;

class MapayController extends PayController
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
                "id" => (int)$this->payGateway->merchant_id,//平台ID号
                "price" => (float)$this->order->actual_price,//原价
                "pay_id" => $this->order->order_sn, //可以是用户ID,站内商户订单号,用户名
                "param" => $this->payGateway->pay_check,//自定义参数
                "act" => 0,//是否开启认证版的免挂机功能
                "outTime" => 120,//二维码超时设置
                "page" => 1,//付款页面展示方式
                'return_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                "pay_type" => 0,//支付宝使用官方接口
                "chart" => 'utf-8'//字符编码方式
                //其他业务参数根据在线开发文档，添加参数.文档地址:https://codepay.fateqq.com/apiword/
                //如"参数名"=>"参数值"
            );
            switch ($payway){
                case 'mqq':
                    $parameter['type'] = 2;
                    break;
                case 'mzfb':
                    $parameter['type'] = 1;
                    break;
                case 'mwx':
                default:
                    $parameter['type'] = 3;
                    break;
            }
            $quri = md5_signquery($parameter, $this->payGateway->merchant_pem);
            $payurl = $this->payGateway->merchant_key . $quri; //支付页面
            return redirect()->away($payurl);
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }


    public function notifyUrl(Request $request)
    {
        $data = $request->post();
        return $this->paymentCallbackService->handleSignedNotification(
            $data['pay_id'],
            '/pay/mapay',
            function ($order, $payGateway) use ($data) {
                $query = signquery_string($data);

                return !empty($data['pay_no']) && md5($query . $payGateway->merchant_pem) == $data['sign'];
            },
            (float) $data['money'],
            $data['pay_id'],
            'fail',
            'fail',
            'success'
        );
    }




}

