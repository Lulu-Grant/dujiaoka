<?php

namespace Tests\Unit;

use App\Models\Goods;
use App\Service\GoodsActionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoodsActionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('coupons_goods')->whereIn('goods_id', [96101])->delete();
        DB::table('coupons')->whereIn('id', [96101])->delete();
        DB::table('goods')->whereIn('id', [96101])->delete();
        DB::table('goods_group')->whereIn('id', [96101])->delete();

        parent::tearDown();
    }

    public function test_clone_defaults_reset_sensitive_fields(): void
    {
        $goods = $this->seedGoodsFixture();

        $defaults = app(GoodsActionService::class)->cloneDefaults($goods);

        $this->assertSame('测试商品 Shell（复制）', $defaults['gd_name']);
        $this->assertSame(0, $defaults['in_stock']);
        $this->assertSame(0, $defaults['sales_volume']);
        $this->assertSame(0, $defaults['is_open']);
        $this->assertSame([96101], $defaults['coupon_ids']);
        $this->assertSame('测试商品简介', $defaults['gd_description']);
    }

    public function test_batch_description_defaults_start_empty_for_safe_review(): void
    {
        $defaults = app(GoodsActionService::class)->batchDescriptionDefaults([96101, 96102]);

        $this->assertSame([96101, 96102], $defaults['goods_ids']);
        $this->assertSame("96101\n96102", $defaults['ids_text']);
        $this->assertSame('', $defaults['description']);
    }

    public function test_batch_keywords_defaults_start_empty_for_safe_review(): void
    {
        $defaults = app(GoodsActionService::class)->batchKeywordsDefaults([96101, 96102]);

        $this->assertSame([96101, 96102], $defaults['goods_ids']);
        $this->assertSame("96101\n96102", $defaults['ids_text']);
        $this->assertSame('', $defaults['gd_keywords']);
    }

    public function test_batch_keywords_suffix_defaults_start_empty_for_safe_review(): void
    {
        $defaults = app(GoodsActionService::class)->batchKeywordsSuffixDefaults([96101, 96102]);

        $this->assertSame([96101, 96102], $defaults['goods_ids']);
        $this->assertSame("96101\n96102", $defaults['ids_text']);
        $this->assertSame('', $defaults['keywords_suffix']);
    }

    public function test_batch_keywords_trim_defaults_only_require_ids_for_safe_review(): void
    {
        $defaults = app(GoodsActionService::class)->batchKeywordsTrimDefaults([96101, 96102]);

        $this->assertSame([96101, 96102], $defaults['goods_ids']);
        $this->assertSame("96101\n96102", $defaults['ids_text']);
        $this->assertArrayNotHasKey('gd_keywords', $defaults);
    }

    private function seedGoodsFixture(): Goods
    {
        DB::table('goods_group')->insert([
            'id' => 96101,
            'gp_name' => '测试分类 Shell',
            'is_open' => 1,
            'ord' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('goods')->insert([
            'id' => 96101,
            'group_id' => 96101,
            'gd_name' => '测试商品 Shell',
            'gd_description' => '测试商品简介',
            'gd_keywords' => '测试关键字',
            'picture' => '/uploads/xigua.png',
            'retail_price' => 99,
            'actual_price' => 79,
            'in_stock' => 20,
            'sales_volume' => 5,
            'ord' => 2,
            'buy_limit_num' => 1,
            'buy_prompt' => '购买提示',
            'description' => '商品说明',
            'type' => 1,
            'wholesale_price_cnf' => "2,70\n5,60",
            'other_ipu_cnf' => "账号\n密码",
            'api_hook' => 'https://example.com/hook',
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons')->insert([
            'id' => 96101,
            'discount' => 8,
            'coupon' => '测试优惠码 Shell',
            'ret' => 1,
            'is_use' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('coupons_goods')->insert([
            'coupons_id' => 96101,
            'goods_id' => 96101,
        ]);

        return Goods::query()->with(['coupon:id,coupon'])->findOrFail(96101);
    }
}
