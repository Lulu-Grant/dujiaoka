<?php

namespace App\Service;

use App\Models\Carmis;
use App\Models\Goods;

class GoodsInventoryService
{
    public function resolveStockFromRow(int $id, int $type, int $inStock): int
    {
        $goods = new Goods();
        $goods->id = $id;
        $goods->type = $type;
        $goods->in_stock = $inStock;

        return $this->resolveStock($goods);
    }

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
