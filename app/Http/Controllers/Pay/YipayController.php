<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\PaymentCallbackService;
use Illuminate\Http\Request;

class YipayController extends PayController
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
            //组装支付参数
            $parameter = [
                'pid' =>  $this->payGateway->merchant_id,
                'type' => $payway,
                'out_trade_no' => $this->order->order_sn,
                'return_url' => route('yipay-return', ['order_id' => $this->order->order_sn]),
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'name'   => $this->order->order_sn,
                'money'  => (float)$this->order->actual_price,
                'sign' => $this->payGateway->merchant_pem,
                'sign_type' =>'MD5'
            ];
            ksort($parameter); //重新排序$data数组
            reset($parameter); //内部指针指向数组中的第一个元素
            $sign = '';
            foreach ($parameter as $key => $val) {
                if ($key == "sign" || $key == "sign_type" || $val == "") continue;
                if ($key != 'sign') {
                    if ($sign != '') {
                        $sign .= "&";
                    }
                    $sign .= "$key=$val"; //拼接为url参数形式
                }
            }

            $sign = md5($sign . $this->payGateway->merchant_pem);//密码追加进入开始MD5签名
            $parameter['sign'] = $sign;
            //待请求参数数组
            $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->payGateway->merchant_key . "' method='get'>";

            foreach($parameter as $key => $val) {
                $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }

            //submit按钮控件请不要含有name属性
            $sHtml = $sHtml."<input type='submit' value=''></form>";
            $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
            return $sHtml;
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    public function notifyUrl(Request $request)
    {
        $data = $request->all();

        return $this->paymentCallbackService->handleSignedNotification(
            $data['out_trade_no'],
            '/pay/yipay',
            function ($order, $payGateway) use ($data) {
                ksort($data);
                reset($data);
                $sign = '';
                foreach ($data as $key => $val) {
                    if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                        continue;
                    }
                    if ($sign != '') {
                        $sign .= "&";
                    }
                    $sign .= "$key=$val";
                }

                return !empty($data['trade_no']) && md5($sign . $payGateway->merchant_pem) == $data['sign'];
            },
            (float) $data['money'],
            $data['trade_no'] ?? '',
            'fail',
            'fail',
            'success'
        );
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('order_id');
        // 有些易支付太垃了，异步通知还没到就跳转了，导致订单显示待支付，其实已经支付了，所以这里休眠2秒
        sleep(2);
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }

}
