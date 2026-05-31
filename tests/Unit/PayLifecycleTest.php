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

    public function test_removed_gateway_is_marked_as_retired(): void
    {
        $this->assertTrue(Pay::isRetiredGateway('paypal'));
        $this->assertFalse(Pay::isLegacyGateway('paypal'));
        $this->assertSame(admin_trans('pay.fields.lifecycle_retired'), Pay::getLifecycleLabel('paypal'));
    }

    public function test_active_gateway_is_marked_as_active(): void
    {
        $this->assertFalse(Pay::isRetiredGateway('alipay'));
        $this->assertFalse(Pay::isLegacyGateway('alipay'));
        $this->assertSame(admin_trans('pay.fields.lifecycle_active'), Pay::getLifecycleLabel('alipay'));
    }

    public function test_maintained_payment_routes_are_limited_to_current_scope(): void
    {
        $this->assertSame([
            '/pay/alipay',
            '/pay/wepay',
            '/pay/yipay',
            '/pay/epusdt',
        ], Pay::MAINTAINED_HANDLEROUTES);
    }
}
