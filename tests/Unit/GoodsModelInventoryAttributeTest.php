<?php

namespace Tests\Unit;

use App\Models\Goods;
use Tests\TestCase;

class GoodsModelInventoryAttributeTest extends TestCase
{
    public function test_in_stock_attribute_prefers_unsold_carmi_count_for_automatic_goods(): void
    {
        $goods = new Goods();
        $goods->type = Goods::AUTOMATIC_DELIVERY;
        $goods->setRawAttributes([
            'in_stock' => 99,
            'type' => Goods::AUTOMATIC_DELIVERY,
            'carmis_count' => 7,
        ]);

        $this->assertSame(7, $goods->in_stock);
    }

    public function test_in_stock_attribute_keeps_stored_inventory_for_manual_goods(): void
    {
        $goods = new Goods();
        $goods->setRawAttributes([
            'in_stock' => 15,
            'type' => Goods::MANUAL_PROCESSING,
            'carmis_count' => 7,
        ]);

        $this->assertSame(15, $goods->in_stock);
    }
}
