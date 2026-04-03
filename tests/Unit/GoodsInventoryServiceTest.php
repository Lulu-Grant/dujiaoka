<?php

namespace Tests\Unit;

use App\Models\Carmis;
use App\Models\Goods;
use App\Service\GoodsInventoryService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoodsInventoryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('carmis')->delete();

        parent::tearDown();
    }

    public function test_resolve_stock_counts_unsold_carmis_for_automatic_goods(): void
    {
        DB::table('carmis')->insert([
            [
                'goods_id' => 1001,
                'carmi' => 'AUTO-1',
                'status' => Carmis::STATUS_UNSOLD,
                'is_loop' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'goods_id' => 1001,
                'carmi' => 'AUTO-2',
                'status' => Carmis::STATUS_SOLD,
                'is_loop' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'goods_id' => 1001,
                'carmi' => 'AUTO-3',
                'status' => Carmis::STATUS_UNSOLD,
                'is_loop' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $goods = new Goods();
        $goods->id = 1001;
        $goods->type = Goods::AUTOMATIC_DELIVERY;
        $goods->in_stock = 99;

        $this->assertSame(2, app(GoodsInventoryService::class)->resolveStock($goods));
    }

    public function test_resolve_stock_returns_stored_inventory_for_manual_goods(): void
    {
        $goods = new Goods();
        $goods->id = 2001;
        $goods->type = Goods::MANUAL_PROCESSING;
        $goods->in_stock = 15;

        $this->assertSame(15, app(GoodsInventoryService::class)->resolveStock($goods));
    }
}
