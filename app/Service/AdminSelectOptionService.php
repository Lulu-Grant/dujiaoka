<?php

namespace App\Service;

use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Pay;

class AdminSelectOptionService
{
    public function goodsOptions(): array
    {
        return Goods::query()->pluck('gd_name', 'id')->toArray();
    }

    public function automaticGoodsOptions(): array
    {
        return Goods::query()
            ->where('type', Goods::AUTOMATIC_DELIVERY)
            ->pluck('gd_name', 'id')
            ->toArray();
    }

    public function couponOptions(): array
    {
        return Coupon::query()->pluck('coupon', 'id')->toArray();
    }

    public function payOptions(): array
    {
        return Pay::query()->pluck('pay_name', 'id')->toArray();
    }

    public function goodsGroupOptions(): array
    {
        return GoodsGroup::query()->pluck('gp_name', 'id')->toArray();
    }
}
