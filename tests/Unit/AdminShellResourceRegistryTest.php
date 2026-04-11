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

    public function test_registry_exposes_permission_except_patterns(): void
    {
        $patterns = AdminShellResourceRegistry::permissionExceptPatterns();

        $this->assertSame(['v2/goods-group*', 'v2/emailtpl*', 'v2/pay*'], $patterns);
    }
}
