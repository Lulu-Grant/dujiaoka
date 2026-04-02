<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\PayjsNotificationService;
use Illuminate\Http\Request;
use Xhat\Payjs\Facades\Payjs;


class PayjsController extends PayController
{
    /**
     * @var \App\Service\PayjsNotificationService
     */
    private $payjsNotificationService;

    public function __construct()
    {
        parent::__construct();
        $this->payjsNotificationService = app(PayjsNotificationService::class);
    }

    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            // 构造订单基础信息
            $data = [
                'body' => $this->order->order_sn,                                // 订单标题
                'total_fee' => bcmul($this->order->actual_price, 100, 0),    // 订单金额
                'out_trade_no' => $this->order->order_sn,                           // 订单号
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
            ];
            config(['payjs.mchid' => $this->payGateway->merchant_id, 'payjs.key' => $this->payGateway->merchant_pem]);
            switch ($payway){
                case 'payjswescan':
                    try{
                        $payres = Payjs::native($data);
                        if ($payres['return_code'] != 1) {
                            throw new RuleValidationException($payres['return_msg']);
                        }
                        $result['payname'] = $this->payGateway->pay_name;
                        $result['actual_price'] = (float)$this->order->actual_price;
                        $result['orderid'] = $this->order->order_sn;
                        $result['qr_code'] = $payres['code_url'];
                        return $this->render('static_pages/qrpay', $result, __('dujiaoka.scan_qrcode_to_pay'));
                    } catch (\Exception $e) {
                        throw new RuleValidationException(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                    }
                    break;
            }
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }


    public function notifyUrl(Request $request)
    {
        return $this->payjsNotificationService->handleNotification(
            (string) $request->input('out_trade_no')
        );
    }

}
