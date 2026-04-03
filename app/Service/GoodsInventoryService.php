<?php

namespace App\Service;

use App\Models\Carmis;
use App\Models\Goods;

class GoodsInventoryService
{
    public function resolveStock(Goods $goods): int
    {
        if ((int) $goods->type === Goods::AUTOMATIC_DELIVERY) {
            return Carmis::query()
                ->where('goods_id', $goods->id)
                ->where('status', Carmis::STATUS_UNSOLD)
                ->count();
        }

        return (int) $goods->in_stock;
    }
}
