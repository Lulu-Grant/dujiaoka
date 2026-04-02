<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use App\Service\CoinbaseWebhookService;
use Illuminate\Http\Request;

class CoinbaseController extends PayController
{
    /**
     * @var \App\Service\CoinbaseWebhookService
     */
    private $coinbaseWebhookService;

    public function __construct()
    {
        parent::__construct();
        $this->coinbaseWebhookService = app(CoinbaseWebhookService::class);
    }

    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            //构造要请求的参数数组，无需改动
            switch ($payway) {
                case 'coinbase':
                default:
                    try {
                        $createOrderUrl="https://api.commerce.coinbase.com/charges";
                        $price_amount = sprintf('%.2f', (float)$this->order->actual_price);// 只取小数点后两位
                        $fees = (double)$this->payGateway->merchant_id;//手续费费率  比如 0.05
                        if($fees>0.00)
                        {
                            $price_amount =(double)$price_amount * (1.00+$fees);// 价格 * （1 + 0.05）
                        }


                        $redirect_url = url('detail-order-sn', ['orderSN' => $this->order->order_sn]);  //同步地址
                        $cancel_url = url('detail-order-sn', ['orderSN' => $this->order->order_sn]);  //同步地址
                        $config = [
                            'name'=>$this->order->title,
                            'description'=>$this->order->title.'需付款'.$price_amount.'元',
                            'pricing_type' => 'fixed_price',
                            'local_price' => [
                                'amount' =>  $price_amount,
                                'currency' => 'CNY'
                            ],
                            'metadata' => [
                                'customer_id' =>  $this->order->order_sn,
                                'customer_name' => $this->order->title
                            ],
                            'redirect_url' =>$redirect_url,
                            'cancel_url'=> $cancel_url
                        ];
                        $header = array();
                        $header[] = 'Content-Type:application/json';
                        $header[] = 'X-CC-Api-Key:'.$this->payGateway->merchant_key; //APP key
                        $header[] = 'X-CC-Version: 2018-03-22';

                        $ch = curl_init(); //使用curl请求
                        curl_setopt($ch, CURLOPT_URL, $createOrderUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config));
                        $coinbase_json = curl_exec($ch);
                        curl_close($ch);

                        $coinbase_date=json_decode($coinbase_json,true);
                        if(is_array($coinbase_date))
                        {
                            $payment_url = $coinbase_date['data']['hosted_url'];
                        }
                        else
                        {
                            return 'fail|Coinbase支付接口请求失败';
                        }
                        return redirect()->away($payment_url);
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
        return $this->coinbaseWebhookService->handleWebhook(
            $request->getContent(),
            (string) $request->header('X-CC-WEBHOOK-SIGNATURE', '')
        );
    }


}
