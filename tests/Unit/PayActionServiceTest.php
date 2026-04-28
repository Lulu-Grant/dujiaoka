<?php

namespace Tests\Unit;

use App\Models\Pay;
use App\Service\PayActionService;
use Tests\TestCase;

class PayActionServiceTest extends TestCase
{
    public function test_create_context_for_copying_preserves_safe_summary(): void
    {
        $service = $this->app->make(PayActionService::class);
        $source = new Pay();
        $source->pay_name = 'Stripe 样板';
        $source->pay_check = 'stripe';

        $context = $service->createContext($source);

        $this->assertSame('复制支付通道：Stripe 样板', $context['summaryTitle']);
        $this->assertStringContainsString('复制来源', $context['summaryItems'][0]['label']);
        $this->assertStringContainsString('Stripe 样板', $context['summaryItems'][0]['value']);
        $this->assertStringContainsString('商户 KEY 和商户 PEM 不会被自动复制', $context['notice']);
    }

    public function test_create_sections_for_copying_keep_secrets_blank(): void
    {
        $service = $this->app->make(PayActionService::class);
        $source = new Pay();
        $source->pay_name = 'Stripe 样板';
        $source->merchant_id = 'merchant-id';
        $source->merchant_key = 'merchant-key';
        $source->merchant_pem = 'merchant-pem';
        $source->pay_check = 'stripe';
        $source->pay_client = Pay::PAY_CLIENT_PC;
        $source->pay_method = Pay::METHOD_JUMP;
        $source->pay_handleroute = '/pay/stripe';
        $source->is_open = Pay::STATUS_OPEN;

        $sections = $service->createSections($source);
        $fields = collect($sections)->pluck('fields')->flatten(1);
        $fieldMap = $fields->keyBy('name');

        $this->assertSame('Stripe 样板（副本）', $fieldMap['pay_name']['value']);
        $this->assertSame('merchant-id', $fieldMap['merchant_id']['value']);
        $this->assertSame('', $fieldMap['merchant_key']['value']);
        $this->assertSame('', $fieldMap['merchant_pem']['value']);
        $this->assertSame('', $fieldMap['pay_check']['value']);
        $this->assertSame('/pay/stripe', $fieldMap['pay_handleroute']['value']);
    }

    public function test_create_defaults_for_copying_keep_secrets_blank(): void
    {
        $service = $this->app->make(PayActionService::class);
        $source = new Pay();
        $source->pay_name = 'Stripe 样板';
        $source->merchant_id = 'merchant-id';
        $source->merchant_key = 'merchant-key';
        $source->merchant_pem = 'merchant-pem';
        $source->pay_check = 'stripe';
        $source->pay_client = Pay::PAY_CLIENT_PC;
        $source->pay_method = Pay::METHOD_JUMP;
        $source->pay_handleroute = '/pay/stripe';
        $source->is_open = Pay::STATUS_OPEN;

        $defaults = $service->createDefaults($source);

        $this->assertSame('Stripe 样板（副本）', $defaults['pay_name']);
        $this->assertSame('merchant-id', $defaults['merchant_id']);
        $this->assertSame('', $defaults['merchant_key']);
        $this->assertSame('', $defaults['merchant_pem']);
        $this->assertSame('', $defaults['pay_check']);
        $this->assertSame('/pay/stripe', $defaults['pay_handleroute']);
    }

    public function test_batch_client_defaults_use_pc_as_safe_default(): void
    {
        $service = $this->app->make(PayActionService::class);

        $defaults = $service->batchClientDefaults([101, 102]);

        $this->assertSame([101, 102], $defaults['pay_ids']);
        $this->assertSame("101\n102", $defaults['ids_text']);
        $this->assertSame(Pay::PAY_CLIENT_PC, $defaults['pay_client']);
    }

    public function test_batch_name_defaults_start_empty_for_safe_review(): void
    {
        $service = $this->app->make(PayActionService::class);

        $defaults = $service->batchNameDefaults([201, 202]);

        $this->assertSame([201, 202], $defaults['pay_ids']);
        $this->assertSame("201\n202", $defaults['ids_text']);
        $this->assertSame('', $defaults['pay_name']);
    }

    public function test_batch_name_prefix_defaults_start_empty_for_safe_review(): void
    {
        $service = $this->app->make(PayActionService::class);

        $defaults = $service->batchNamePrefixDefaults([301, 302]);

        $this->assertSame([301, 302], $defaults['pay_ids']);
        $this->assertSame("301\n302", $defaults['ids_text']);
        $this->assertSame('', $defaults['name_prefix']);
    }

    public function test_batch_name_suffix_defaults_start_empty_for_safe_review(): void
    {
        $service = $this->app->make(PayActionService::class);

        $defaults = $service->batchNameSuffixDefaults([401, 402]);

        $this->assertSame([401, 402], $defaults['pay_ids']);
        $this->assertSame("401\n402", $defaults['ids_text']);
        $this->assertSame('', $defaults['name_suffix']);
    }

    public function test_batch_name_replace_defaults_start_empty_for_safe_review(): void
    {
        $service = $this->app->make(PayActionService::class);

        $defaults = $service->batchNameReplaceDefaults([501, 502]);

        $this->assertSame([501, 502], $defaults['pay_ids']);
        $this->assertSame("501\n502", $defaults['ids_text']);
        $this->assertSame('', $defaults['search_text']);
        $this->assertSame('', $defaults['replace_text']);
    }
}
