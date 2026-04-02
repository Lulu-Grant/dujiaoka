<?php

namespace App\Service;

use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderCreationService
{
    /**
     * 汇总订单价格明细
     *
     * @param Goods $goods
     * @param int $buyAmount
     * @param Coupon|null $coupon
     * @return array<string, float>
     */
    public function buildPricing(Goods $goods, int $buyAmount, ?Coupon $coupon): array
    {
        $totalPrice = $this->calculateTotalPrice($goods, $buyAmount);
        $couponPrice = $this->calculateCouponPrice($coupon);
        $wholesalePrice = $this->calculateWholesalePrice($goods, $buyAmount, $totalPrice);

        return [
            'total_price' => $totalPrice,
            'coupon_discount_price' => $couponPrice,
            'wholesale_discount_price' => $wholesalePrice,
            'actual_price' => $this->calculateActualPrice($totalPrice, $couponPrice, $wholesalePrice),
        ];
    }

    /**
     * 创建未保存的待支付订单
     *
     * @param Goods $goods
     * @param int $buyAmount
     * @param Coupon|null $coupon
     * @param array<string, float> $pricing
     * @param array<string, mixed> $attributes
     * @return Order
     */
    public function makePendingOrder(
        Goods $goods,
        int $buyAmount,
        ?Coupon $coupon,
        array $pricing,
        array $attributes
    ): Order {
        $order = new Order();
        $order->order_sn = strtoupper(Str::random(16));
        $order->goods_id = $goods->id;
        $order->title = $this->buildOrderTitle($goods, $buyAmount);
        $order->type = $goods->type;
        $order->goods_price = $goods->actual_price;
        $order->buy_amount = $buyAmount;
        $order->search_pwd = $attributes['search_pwd'];
        $order->email = $attributes['email'];
        $order->pay_id = $attributes['pay_id'];
        $order->info = $attributes['info'];
        $order->buy_ip = $attributes['buy_ip'];
        $order->coupon_discount_price = $pricing['coupon_discount_price'];
        $order->wholesale_discount_price = $pricing['wholesale_discount_price'];
        $order->total_price = $pricing['total_price'];
        $order->actual_price = $pricing['actual_price'];

        if ($coupon) {
            $order->coupon_id = $coupon->id;
        }

        return $order;
    }

    /**
     * 订单标题
     *
     * @param Goods $goods
     * @param int $buyAmount
     * @return string
     */
    public function buildOrderTitle(Goods $goods, int $buyAmount): string
    {
        return $goods->gd_name . ' x ' . $buyAmount;
    }

    /**
     * 计算优惠码价格
     *
     * @param Coupon|null $coupon
     * @return float
     */
    private function calculateCouponPrice(?Coupon $coupon): float
    {
        if (!$coupon) {
            return 0;
        }

        return (float) $coupon->discount;
    }

    /**
     * 计算批发优惠
     *
     * @param Goods $goods
     * @param int $buyAmount
     * @param float $totalPrice
     * @return float
     */
    private function calculateWholesalePrice(Goods $goods, int $buyAmount, float $totalPrice): float
    {
        $wholesalePrice = 0;
        $wholesaleTotalPrice = 0;

        if ($goods->wholesale_price_cnf) {
            $formatWholesalePrice = format_wholesale_price($goods->wholesale_price_cnf);
            foreach ($formatWholesalePrice as $item) {
                if ($buyAmount >= $item['number']) {
                    $wholesalePrice = $item['price'];
                }
            }
        }

        if ($wholesalePrice > 0) {
            $newTotalPrice = bcmul($wholesalePrice, $buyAmount, 2);
            $wholesaleTotalPrice = bcsub($totalPrice, $newTotalPrice, 2);
        }

        return (float) $wholesaleTotalPrice;
    }

    /**
     * 订单总价
     *
     * @param Goods $goods
     * @param int $buyAmount
     * @return float
     */
    private function calculateTotalPrice(Goods $goods, int $buyAmount): float
    {
        return (float) bcmul($goods->actual_price, $buyAmount, 2);
    }

    /**
     * 计算实际支付金额
     *
     * @param float $totalPrice
     * @param float $couponPrice
     * @param float $wholesalePrice
     * @return float
     */
    private function calculateActualPrice(float $totalPrice, float $couponPrice, float $wholesalePrice): float
    {
        $actualPrice = bcsub($totalPrice, $couponPrice, 2);
        $actualPrice = bcsub($actualPrice, $wholesalePrice, 2);

        if ($actualPrice <= 0) {
            $actualPrice = 0;
        }

        return (float) $actualPrice;
    }
}
