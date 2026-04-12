<?php

namespace Tests\Unit;

use App\Service\AdminShellResourceRegistry;
use Tests\TestCase;

class AdminShellResourceRegistryTest extends TestCase
{
    public function test_registry_returns_goods_group_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('goods-group');

        $this->assertSame(\App\Service\AdminShellGoodsGroupPageService::class, $resource['service']);
        $this->assertTrue($resource['uses_scope']);
    }

    public function test_registry_returns_email_template_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('emailtpl');

        $this->assertSame(\App\Service\AdminShellEmailTemplatePageService::class, $resource['service']);
        $this->assertFalse($resource['uses_scope']);
    }

    public function test_registry_returns_pay_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('pay');

        $this->assertSame(\App\Service\AdminShellPayPageService::class, $resource['service']);
        $this->assertTrue($resource['uses_scope']);
    }

    public function test_registry_returns_coupon_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('coupon');

        $this->assertSame(\App\Service\AdminShellCouponPageService::class, $resource['service']);
        $this->assertTrue($resource['uses_scope']);
    }

    public function test_registry_returns_carmis_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('carmis');

        $this->assertSame(\App\Service\AdminShellCarmisPageService::class, $resource['service']);
        $this->assertTrue($resource['uses_scope']);
    }

    public function test_registry_returns_system_setting_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('system-setting');

        $this->assertSame(\App\Service\AdminShellSystemSettingPageService::class, $resource['service']);
        $this->assertFalse($resource['uses_scope']);
        $this->assertSame('system-setting', $resource['legacy_uri']);
    }

    public function test_registry_returns_email_test_resource_definition(): void
    {
        $resource = $this->app->make(AdminShellResourceRegistry::class)->get('email-test');

        $this->assertSame(\App\Service\AdminShellEmailTestPageService::class, $resource['service']);
        $this->assertFalse($resource['uses_scope']);
        $this->assertSame('email-test', $resource['legacy_uri']);
    }

    public function test_registry_exposes_permission_except_patterns(): void
    {
        $patterns = AdminShellResourceRegistry::permissionExceptPatterns();

        $this->assertSame(['v2/goods-group*', 'v2/emailtpl*', 'v2/pay*', 'v2/coupon*', 'v2/carmis*', 'v2/system-setting*', 'v2/email-test*'], $patterns);
    }

    public function test_registry_exposes_navigation_items(): void
    {
        $items = AdminShellResourceRegistry::navigationItems();

        $this->assertSame('商品分类管理', $items[0]['label']);
        $this->assertStringEndsWith('v2/goods-group', $items[0]['href']);
        $this->assertSame('admin/v2/pay*', $items[2]['active_pattern']);
        $this->assertSame('优惠码管理', $items[3]['label']);
        $this->assertSame('卡密管理', $items[4]['label']);
        $this->assertSame('系统设置概览', $items[5]['label']);
        $this->assertSame('邮件测试概览', $items[6]['label']);
    }

    public function test_registry_exposes_page_metadata(): void
    {
        $goodsGroup = AdminShellResourceRegistry::definitions()['goods-group'];

        $this->assertSame('商品分类管理', $goodsGroup['index_title']);
        $this->assertSame('商品分类详情', $goodsGroup['show_title']);
        $this->assertSame('Admin Shell', AdminShellResourceRegistry::navigationSectionLabel());
    }
}
