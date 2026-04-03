<?php

namespace Tests\Unit;

use App\Service\AdminSelectOptionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSelectOptionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('goods')->delete();
        DB::table('goods_group')->delete();
        DB::table('coupons')->delete();
        DB::table('pays')->delete();
    }

    protected function tearDown(): void
    {
        DB::table('goods')->delete();
        DB::table('goods_group')->delete();
        DB::table('coupons')->delete();
        DB::table('pays')->delete();

        parent::tearDown();
    }

    public function test_service_exposes_admin_select_options(): void
    {
        DB::table('goods_group')->insert([
            'id' => 3001,
            'gp_name' => 'Group A',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('goods')->insert([
            [
                'id' => 3001,
                'gd_name' => 'Auto Product',
                'gd_description' => 'desc',
                'gd_keywords' => 'kw',
                'group_id' => 3001,
                'type' => 1,
                'retail_price' => 10,
                'actual_price' => 9,
                'in_stock' => 0,
                'sales_volume' => 0,
                'buy_limit_num' => 0,
                'buy_prompt' => '',
                'description' => '',
                'other_ipu_cnf' => '',
                'wholesale_price_cnf' => '',
                'api_hook' => '',
                'ord' => 1,
                'is_open' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3002,
                'gd_name' => 'Manual Product',
                'gd_description' => 'desc',
                'gd_keywords' => 'kw',
                'group_id' => 3001,
                'type' => 2,
                'retail_price' => 10,
                'actual_price' => 9,
                'in_stock' => 5,
                'sales_volume' => 0,
                'buy_limit_num' => 0,
                'buy_prompt' => '',
                'description' => '',
                'other_ipu_cnf' => '',
                'wholesale_price_cnf' => '',
                'api_hook' => '',
                'ord' => 1,
                'is_open' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
        DB::table('coupons')->insert([
            'id' => 3001,
            'discount' => 1,
            'is_use' => 0,
            'is_open' => 1,
            'coupon' => 'SAVE1',
            'ret' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        DB::table('pays')->insert([
            'id' => 3001,
            'pay_name' => 'Stripe',
            'merchant_id' => 'mid',
            'merchant_key' => 'mkey',
            'merchant_pem' => 'mpem',
            'pay_check' => 'stripe',
            'pay_client' => 1,
            'pay_method' => 1,
            'pay_handleroute' => '/pay/stripe',
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $service = app(AdminSelectOptionService::class);

        $this->assertSame(['3001' => 'Auto Product', '3002' => 'Manual Product'], $service->goodsOptions());
        $this->assertSame(['3001' => 'Auto Product'], $service->automaticGoodsOptions());
        $this->assertSame(['3001' => 'SAVE1'], $service->couponOptions());
        $this->assertSame(['3001' => 'Stripe'], $service->payOptions());
        $this->assertSame(['3001' => 'Group A'], $service->goodsGroupOptions());
    }
}
