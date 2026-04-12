<?php

namespace App\Service;

use App\Models\Coupon;

class CouponActionService
{
    public function createDefaults(): array
    {
        return [
            'goods_ids' => [],
            'discount' => 0,
            'coupon' => '',
            'ret' => 1,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => Coupon::STATUS_OPEN,
        ];
    }

    public function editDefaults(Coupon $coupon): array
    {
        return [
            'goods_ids' => $coupon->goods->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all(),
            'discount' => $coupon->discount,
            'coupon' => $coupon->coupon,
            'ret' => $coupon->ret,
            'is_use' => $coupon->is_use,
            'is_open' => $coupon->is_open,
        ];
    }

    public function create(array $payload): Coupon
    {
        $coupon = new Coupon();
        $coupon->discount = $payload['discount'];
        $coupon->coupon = $payload['coupon'];
        $coupon->ret = $payload['ret'];
        $coupon->is_use = $payload['is_use'];
        $coupon->is_open = $payload['is_open'];
        $coupon->save();
        $coupon->goods()->sync($payload['goods_ids']);

        return $coupon->fresh('goods');
    }

    public function update(Coupon $coupon, array $payload): Coupon
    {
        $coupon->discount = $payload['discount'];
        $coupon->coupon = $payload['coupon'];
        $coupon->ret = $payload['ret'];
        $coupon->is_use = $payload['is_use'];
        $coupon->is_open = $payload['is_open'];
        $coupon->save();
        $coupon->goods()->sync($payload['goods_ids']);

        return $coupon->fresh('goods');
    }
}
