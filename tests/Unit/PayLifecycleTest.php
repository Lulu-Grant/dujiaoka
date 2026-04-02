<?php

namespace Tests\Unit;

use App\Models\Pay;
use Tests\TestCase;

class PayLifecycleTest extends TestCase
{
    public function test_retired_gateway_is_marked_as_retired(): void
    {
        $this->assertTrue(Pay::isRetiredGateway('payjs'));
        $this->assertSame(admin_trans('pay.fields.lifecycle_retired'), Pay::getLifecycleLabel('payjs'));
    }

    public function test_legacy_gateway_is_marked_as_legacy(): void
    {
        $this->assertTrue(Pay::isLegacyGateway('paypal'));
        $this->assertSame(admin_trans('pay.fields.lifecycle_legacy'), Pay::getLifecycleLabel('paypal'));
    }

    public function test_active_gateway_is_marked_as_active(): void
    {
        $this->assertFalse(Pay::isRetiredGateway('alipay'));
        $this->assertFalse(Pay::isLegacyGateway('alipay'));
        $this->assertSame(admin_trans('pay.fields.lifecycle_active'), Pay::getLifecycleLabel('alipay'));
    }
}
