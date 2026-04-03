<?php

namespace Tests\Unit;

use Tests\TestCase;

class StripeCheckoutViewTest extends TestCase
{
    public function test_stripe_checkout_view_uses_local_assets_for_page_shell(): void
    {
        $html = view('stripe.checkout', [
            'publishable_key' => 'pk_test',
            'orderid' => 'STRIPE-VIEW-ASSET-001',
            'amount_cny' => 1000,
            'amount_usd' => 130,
            'price' => 10.00,
            'source_currency' => 'cny',
            'target_currency' => 'usd',
            'return_url' => 'https://example.com/pay/stripe/return_url/?orderid=STRIPE-VIEW-ASSET-001',
            'detail_url' => 'https://example.com/detail-order-sn/STRIPE-VIEW-ASSET-001',
            'check_url' => 'https://example.com/pay/stripe/check',
            'charge_url' => 'https://example.com/pay/stripe/charge',
        ])->render();

        $this->assertStringContainsString('/assets/avatar/css/stripe-checkout.css', $html);
        $this->assertStringContainsString('/assets/avatar/js/jquery-3.6.0.min.js', $html);
        $this->assertStringContainsString('/vendor/dcat-admin/dcat/plugins/jquery-qrcode/dist/jquery-qrcode.min.js', $html);
        $this->assertStringContainsString('/assets/avatar/js/stripe-checkout.js', $html);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
    }
}
