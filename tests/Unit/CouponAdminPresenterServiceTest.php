<?php

namespace Tests\Unit;

use App\Service\CouponAdminPresenterService;
use Tests\TestCase;

class CouponAdminPresenterServiceTest extends TestCase
{
    public function test_selected_goods_ids_returns_empty_array_for_empty_value(): void
    {
        $this->assertSame([], app(CouponAdminPresenterService::class)->selectedGoodsIds(null));
    }

    public function test_selected_goods_ids_extracts_relation_ids(): void
    {
        $value = [
            ['id' => 1, 'gd_name' => 'A'],
            ['id' => 2, 'gd_name' => 'B'],
        ];

        $this->assertSame([1, 2], app(CouponAdminPresenterService::class)->selectedGoodsIds($value));
    }
}
