<?php

namespace Tests\Unit;

use App\Service\CouponActionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CouponActionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        $batchIds = DB::table('coupons')->where('coupon', 'like', 'XIGUA-BATCH-UNIT-%')->pluck('id')->all();
        if (!empty($batchIds)) {
            DB::table('coupons_goods')->whereIn('coupons_id', $batchIds)->delete();
        }
        DB::table('coupons')->where('coupon', 'like', 'XIGUA-BATCH-UNIT-%')->delete();
        DB::table('goods')->where('id', 95101)->delete();

        parent::tearDown();
    }

    public function test_batch_create_defaults_provide_safe_generation_values(): void
    {
        $defaults = app(CouponActionService::class)->batchCreateDefaults();

        $this->assertSame([], $defaults['goods_ids']);
        $this->assertSame(10, $defaults['quantity']);
        $this->assertSame('XIGUA-', $defaults['prefix']);
        $this->assertSame(6, $defaults['length']);
        $this->assertSame(1, $defaults['ret']);
    }

    public function test_create_batch_creates_multiple_coupons_with_shared_payload(): void
    {
        DB::table('goods')->updateOrInsert(
            ['id' => 95101],
            [
                'group_id' => 1,
                'gd_name' => '批量测试商品',
                'gd_description' => 'desc',
                'gd_keywords' => 'key',
                'picture' => null,
                'retail_price' => 10,
                'actual_price' => 10,
                'in_stock' => 0,
                'sales_volume' => 0,
                'ord' => 1,
                'buy_limit_num' => 0,
                'buy_prompt' => null,
                'description' => 'inst',
                'type' => 1,
                'wholesale_price_cnf' => null,
                'other_ipu_cnf' => null,
                'api_hook' => null,
                'is_open' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $coupons = app(CouponActionService::class)->createBatch([
            'goods_ids' => [95101],
            'discount' => 7.5,
            'quantity' => 3,
            'prefix' => 'XIGUA-BATCH-UNIT-',
            'length' => 4,
            'ret' => 2,
            'is_use' => 1,
            'is_open' => 1,
        ]);

        $this->assertCount(3, $coupons);
        $this->assertSame(3, $coupons->filter(function ($coupon) {
            return strpos($coupon->coupon, 'XIGUA-BATCH-UNIT-') === 0;
        })->count());
        $this->assertSame([95101], $coupons->first()->goods->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all());
    }
}
