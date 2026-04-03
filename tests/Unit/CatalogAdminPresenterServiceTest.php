<?php

namespace Tests\Unit;

use App\Service\CatalogAdminPresenterService;
use Tests\TestCase;

class CatalogAdminPresenterServiceTest extends TestCase
{
    public function test_goods_type_label_maps_known_types(): void
    {
        $service = app(CatalogAdminPresenterService::class);

        $this->assertSame(admin_trans('goods.fields.automatic_delivery'), $service->goodsTypeLabel(1));
        $this->assertSame(admin_trans('goods.fields.manual_processing'), $service->goodsTypeLabel(2));
    }

    public function test_carmi_status_and_loop_labels_map_expected_values(): void
    {
        $service = app(CatalogAdminPresenterService::class);

        $this->assertSame(admin_trans('carmis.fields.status_unsold'), $service->carmiStatusLabel(1));
        $this->assertSame(admin_trans('carmis.fields.status_sold'), $service->carmiStatusLabel(2));
        $this->assertSame(admin_trans('carmis.fields.yes'), $service->loopLabel(1));
        $this->assertSame('', $service->loopLabel(0));
    }
}
