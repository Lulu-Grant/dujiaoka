<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\WepayController;
use App\Service\WepayNotificationService;
use Mockery;
use Tests\TestCase;

class WepayControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_notify_url_delegates_to_wepay_notification_service(): void
    {
        $mock = Mockery::mock(WepayNotificationService::class);
        $mock->shouldReceive('handleNotification')
            ->once()
            ->with('WEPAY-ORDER-001')
            ->andReturn('success');

        $this->app->instance(WepayNotificationService::class, $mock);

        $xml = <<<XML
<xml>
    <out_trade_no>WEPAY-ORDER-001</out_trade_no>
</xml>
XML;

        $tempFile = tempnam(sys_get_temp_dir(), 'wepay-xml-');
        file_put_contents($tempFile, $xml);

        $controller = new class($tempFile) extends WepayController {
            private $payloadPath;

            public function __construct(string $payloadPath)
            {
                $this->payloadPath = $payloadPath;
                parent::__construct();
            }

            public function notifyUrl()
            {
                $xml = file_get_contents($this->payloadPath);
                $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

                return app(WepayNotificationService::class)->handleNotification((string) ($arr['out_trade_no'] ?? ''));
            }
        };

        $response = $controller->notifyUrl();

        @unlink($tempFile);

        $this->assertSame('success', $response);
    }
}
