<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalWebhookService
{
    /**
     * PayPal 在当前遗留实现中仍以同步 return 完成为主。
     * 这里先把 webhook 请求读取、归一化和日志行为服务化，
     * 为后续正式替换接入方式留下稳定切口。
     */
    public function handleWebhook(Request $request): void
    {
        $payload = $this->normalizePayload($request);

        if (!empty($payload)) {
            Log::debug('paypal webhook ignored', ['payload' => $payload]);
            return;
        }

        Log::debug('paypal webhook ignored: empty payload');
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizePayload(Request $request): array
    {
        $payload = $request->all();
        if (!empty($payload)) {
            return $payload;
        }

        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}
