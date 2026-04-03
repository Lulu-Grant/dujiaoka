<?php

namespace Tests\Unit;

use App\Service\PayAdminPresenterService;
use Tests\TestCase;

class PayAdminPresenterServiceTest extends TestCase
{
    public function test_lifecycle_badge_uses_expected_variants(): void
    {
        $service = app(PayAdminPresenterService::class);

        $this->assertStringContainsString('badge-danger', $service->lifecycleBadge('payjs'));
        $this->assertStringContainsString('badge-warning', $service->lifecycleBadge('stripe'));
        $this->assertStringContainsString('badge-success', $service->lifecycleBadge('wepay'));
    }

    public function test_client_method_and_status_labels_use_map_based_output(): void
    {
        $service = app(PayAdminPresenterService::class);

        $this->assertSame(admin_trans('pay.fields.pay_client_mobile'), $service->clientLabel(2));
        $this->assertSame(admin_trans('pay.fields.method_scan'), $service->methodLabel(2));
        $this->assertSame(admin_trans('dujiaoka.status_close'), $service->openStatusLabel(0));
    }
}
