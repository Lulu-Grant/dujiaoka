<?php

namespace Tests\Unit;

use App\Service\PaypalWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PaypalWebhookServiceTest extends TestCase
{
    public function test_normalize_payload_prefers_request_input(): void
    {
        $request = Request::create('/pay/paypal/notify_url', 'POST', [
            'id' => 'WH-001',
            'event_type' => 'PAYMENT.SALE.COMPLETED',
        ]);

        $payload = app(PaypalWebhookService::class)->normalizePayload($request);

        $this->assertSame('WH-001', $payload['id']);
        $this->assertSame('PAYMENT.SALE.COMPLETED', $payload['event_type']);
    }

    public function test_normalize_payload_falls_back_to_json_body(): void
    {
        $request = Request::create(
            '/pay/paypal/notify_url',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['id' => 'WH-JSON-001', 'resource_type' => 'sale'])
        );

        $payload = app(PaypalWebhookService::class)->normalizePayload($request);

        $this->assertSame('WH-JSON-001', $payload['id']);
        $this->assertSame('sale', $payload['resource_type']);
    }

    public function test_handle_webhook_logs_empty_payload_as_ignored(): void
    {
        Log::spy();
        $request = Request::create('/pay/paypal/notify_url', 'POST');

        app(PaypalWebhookService::class)->handleWebhook($request);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with('paypal webhook ignored: empty payload');
    }
}
