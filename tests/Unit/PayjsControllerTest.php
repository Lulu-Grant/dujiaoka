<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\PayjsController;
use App\Service\PayjsNotificationService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class PayjsControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_notify_url_delegates_to_payjs_notification_service(): void
    {
        $mock = Mockery::mock(PayjsNotificationService::class);
        $mock->shouldReceive('handleNotification')
            ->once()
            ->with('PAYJS-ORDER-001')
            ->andReturn('success');

        $this->app->instance(PayjsNotificationService::class, $mock);

        $request = Request::create('/pay/payjs/notify_url', 'POST', [
            'out_trade_no' => 'PAYJS-ORDER-001',
        ]);

        $response = app(PayjsController::class)->notifyUrl($request);

        $this->assertSame('success', $response);
    }
}
