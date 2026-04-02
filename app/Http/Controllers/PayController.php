<?php

namespace App\Http\Controllers;

use App\Exceptions\RuleValidationException;
use App\Models\Order;
use App\Service\PayEntryService;

class PayController extends BaseController
{

    /**
     * 支付网关
     * @var \App\Models\Pay
     */
    protected $payGateway;


    /**
     * 订单
     * @var \App\Models\Order
     */
    protected $order;

    /**
     * 支付入口应用服务
     * @var \App\Service\PayEntryService
     */
    protected $payEntryService;


    public function __construct()
    {
        $this->payEntryService = app(PayEntryService::class);
    }

    /**
     * 订单检测
     *
     * @param string $orderSN
     * @throws RuleValidationException
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function checkOrder(string $orderSN)
    {
        $this->order = $this->payEntryService->requirePayableOrder($orderSN);
    }

    /**
     * 加载支付网关
     *
     * @param string $orderSN 订单号
     * @param string $payCheck 支付标识
     * @throws RuleValidationException
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function loadGateWay(string $orderSN, string $payCheck)
    {
        [$this->order, $this->payGateway] = $this->payEntryService->loadGatewayForOrder($orderSN, $payCheck);
    }

    /**
     * 网关处理.
     *
     * @param string $handle 跳转方法
     * @param string $payway 支付标识
     * @param string $orderSN 订单.
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function redirectGateway(string $handle,string $payway, string $orderSN)
    {
        try {
            $this->checkOrder($orderSN);
            $bccomp = bccomp($this->order->actual_price, 0.00, 2);
            // 如果订单金额为0 代表无需支付，直接成功
            if ($bccomp == 0) {
                $this->payEntryService->completeFreeOrder($this->order->order_sn);
                return redirect(url('detail-order-sn', ['orderSN' => $this->order->order_sn]));
            }
            return redirect(url(urldecode($handle), ['payway' => $payway, 'orderSN' => $orderSN]));
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }

    }

}
