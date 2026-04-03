<?php

namespace Tests\Unit;

use App\Service\AdminStatusPresenterService;
use Tests\TestCase;

class AdminStatusPresenterServiceTest extends TestCase
{
    public function test_open_status_label_maps_open_and_close_states(): void
    {
        $service = app(AdminStatusPresenterService::class);

        $this->assertSame(admin_trans('dujiaoka.status_open'), $service->openStatusLabel(1));
        $this->assertSame(admin_trans('dujiaoka.status_close'), $service->openStatusLabel(0));
    }

    public function test_coupon_usage_label_maps_coupon_states(): void
    {
        $service = app(AdminStatusPresenterService::class);

        $this->assertSame(admin_trans('coupon.fields.status_unused'), $service->couponUsageLabel(1));
        $this->assertSame(admin_trans('coupon.fields.status_use'), $service->couponUsageLabel(2));
    }
}
