<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

namespace App\Service;

use App\Exceptions\RuleValidationException;
use App\Models\Coupon;
use App\Models\Order;
use App\Service\DataTransferObjects\CreateOrderData;
use Illuminate\Support\Facades\DB;

/**
 * 订单处理层
 *
 * Class OrderProcessService
 * @package App\Service
 * @author: Assimon
 * @email: Ashang@utf8.hk
 * @blog: https://utf8.hk
 * Date: 2021/5/30
 */
class OrderProcessService
{

    const PENDING_CACHE_KEY = 'PENDING_ORDERS_LIST';

    /**
     * 优惠码服务层
     * @var \App\Service\CouponService
     */
    private $couponService;

    /**
     * 订单创建服务层
     * @var \App\Service\OrderCreationService
     */
    private $orderCreationService;

    /**
     * 订单通知服务层
     * @var \App\Service\OrderNotificationService
     */
    private $orderNotificationService;

    /**
     * 订单支付服务层
     * @var \App\Service\OrderPaymentService
     */
    private $orderPaymentService;

    public function __construct()
    {
        $this->couponService = app('Service\CouponService');
        $this->orderCreationService = app(OrderCreationService::class);
        $this->orderNotificationService = app(OrderNotificationService::class);
        $this->orderPaymentService = app(OrderPaymentService::class);

    }

    /**
     * 标记优惠码已使用
     */
    private function consumeCoupon(?Coupon $coupon): void
    {
        if (!$coupon) {
            return;
        }

        $this->couponService->used($coupon->coupon);
        $this->couponService->retDecr($coupon->coupon);
    }

    /**
     * 使用明确输入对象创建订单
     *
     * @param CreateOrderData $data
     * @return Order
     * @throws RuleValidationException
     */
    public function createOrderFromData(CreateOrderData $data): Order
    {
        try {
            $pricing = $this->orderCreationService->buildPricing($data->goods, $data->buyAmount, $data->coupon);
            $order = $this->orderCreationService->makePendingOrder(
                $data->goods,
                $data->buyAmount,
                $data->coupon,
                $pricing,
                $this->buildOrderAttributes($data)
            );
            $order->save();
            $this->consumeCoupon($data->coupon);
            return $order;
        } catch (\Exception $exception) {
            throw new RuleValidationException($exception->getMessage());
        }

    }

    /**
     * 创建订单时的基础属性
     *
     * @param CreateOrderData $data
     * @return array<string, mixed>
     */
    private function buildOrderAttributes(CreateOrderData $data): array
    {
        return [
            'search_pwd' => $data->searchPwd,
            'email' => $data->email,
            'pay_id' => $data->payID,
            'info' => $data->otherIpt,
            'buy_ip' => $data->buyIP,
        ];
    }

    /**
     * 订单成功方法
     *
     * @param string $orderSN 订单号
     * @param float $actualPrice 实际支付金额
     * @param string $tradeNo 第三方订单号
     * @return Order
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function completedOrder(string $orderSN, float $actualPrice, string $tradeNo = '')
    {
        DB::beginTransaction();
        try {
            $completedOrder = $this->orderPaymentService->completePayment($orderSN, $actualPrice, $tradeNo);
            DB::commit();
            $this->orderNotificationService->dispatchOrderSideEffects($completedOrder);
            return $completedOrder;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new RuleValidationException($exception->getMessage());
        }
    }
}
