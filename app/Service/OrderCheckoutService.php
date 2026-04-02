<?php

namespace App\Service;

use App\Models\Order;
use App\Service\DataTransferObjects\CreateOrderData;
use Illuminate\Http\Request;

class OrderCheckoutService
{
    /**
     * @var \App\Service\OrderService
     */
    private $orderService;

    /**
     * @var \App\Service\OrderProcessService
     */
    private $orderProcessService;

    public function __construct()
    {
        $this->orderService = app('Service\OrderService');
        $this->orderProcessService = app('Service\OrderProcessService');
    }

    /**
     * 从请求构建创建订单输入对象
     *
     * @param Request $request
     * @return CreateOrderData
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Exceptions\RuleValidationException
     */
    public function buildCreateOrderData(Request $request): CreateOrderData
    {
        $this->orderService->validatorCreateOrder($request);
        $goods = $this->orderService->validatorGoods($request);
        $this->orderService->validatorLoopCarmis($request);
        $coupon = $this->orderService->validatorCoupon($request);
        $otherIpt = $this->orderService->validatorChargeInput($goods, $request);

        return new CreateOrderData(
            $goods,
            $coupon,
            $otherIpt,
            (int) $request->input('by_amount'),
            (int) $request->input('payway'),
            (string) $request->input('email'),
            $request->getClientIp(),
            (string) $request->input('search_pwd', '')
        );
    }

    /**
     * 从请求直接创建订单
     *
     * @param Request $request
     * @return Order
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Exceptions\RuleValidationException
     */
    public function createOrderFromRequest(Request $request): Order
    {
        return $this->orderProcessService->createOrderFromData($this->buildCreateOrderData($request));
    }
}
