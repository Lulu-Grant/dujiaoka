<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Service\OrderCreationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCreationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_build_pricing_combines_coupon_and_wholesale_discount(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Pricing Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Pricing Product',
            'gd_description' => 'Pricing Product Description',
            'gd_keywords' => 'pricing,product',
            'actual_price' => 10.00,
            'wholesale_price_cnf' => "3=8.50\n5=8.00",
            'in_stock' => 100,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $coupon = Coupon::query()->create([
            'discount' => 5.00,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => BaseModel::STATUS_OPEN,
            'coupon' => 'SAVE5PLUS',
            'ret' => 2,
        ]);

        $service = app(OrderCreationService::class);
        $pricing = $service->buildPricing($goods, 5, $coupon);

        $this->assertEquals(50.00, $pricing['total_price']);
        $this->assertEquals(5.00, $pricing['coupon_discount_price']);
        $this->assertEquals(10.00, $pricing['wholesale_discount_price']);
        $this->assertEquals(35.00, $pricing['actual_price']);
    }
}
