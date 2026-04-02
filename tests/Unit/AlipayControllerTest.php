<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\AlipayController;
use App\Service\AlipayNotificationService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AlipayControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_notify_url_delegates_to_alipay_notification_service(): void
    {
        $mock = Mockery::mock(AlipayNotificationService::class);
        $mock->shouldReceive('handleNotification')
            ->once()
            ->with('ALIPAY-ORDER-001')
            ->andReturn('success');

        $this->app->instance(AlipayNotificationService::class, $mock);

        $request = Request::create('/pay/alipay/notify_url', 'POST', [
            'out_trade_no' => 'ALIPAY-ORDER-001',
        ]);

        $response = app(AlipayController::class)->notifyUrl($request);

        $this->assertSame('success', $response);
    }
}
