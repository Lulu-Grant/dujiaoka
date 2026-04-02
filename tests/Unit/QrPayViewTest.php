<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QrPayViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('system-setting', [
            'template' => 'avatar',
            'text_logo' => '独角数卡西瓜版',
            'language' => 'zh_CN',
            'order_expire_time' => 5,
        ]);
    }

    public function test_qrpay_view_renders_local_qrcode_container_without_backend_generator(): void
    {
        $html = view('avatar.static_pages.qrpay', [
            'qr_code' => 'weixin://wxpay/test-code',
            'actual_price' => '12.34',
            'orderid' => 'TEST-QR-ORDER',
        ])->render();

        $this->assertStringContainsString('id="pay-qrcode"', $html);
        $this->assertStringContainsString('/vendor/dcat-admin/dcat/plugins/jquery-qrcode/dist/jquery-qrcode.min.js', $html);
        $this->assertStringContainsString('data-qr-code="weixin://wxpay/test-code"', $html);
        $this->assertStringNotContainsString('QrCode::format', $html);
    }
}
