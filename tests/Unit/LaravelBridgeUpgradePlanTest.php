<?php

namespace Tests\Unit;

use Tests\TestCase;

class LaravelBridgeUpgradePlanTest extends TestCase
{
    public function test_bridge_plan_records_php81_verification_and_laravel_sequence(): void
    {
        $contents = file_get_contents(base_path('docs/laravel-bridge-upgrade-plan.md'));

        foreach ([
            'PHP 8.1 已可运行 Composer 平台检查、artisan、全量 PHPUnit 和后台 smoke',
            '推荐桥接顺序是先 Laravel 7，再评估 Laravel 8',
            'sh scripts/composer81-docker check-platform-reqs',
            'sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml',
            'APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell',
            'OK (413 tests, 4245 assertions)',
        ] as $needle) {
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_bridge_plan_records_laravel7_and_laravel8_blockers(): void
    {
        $contents = file_get_contents(base_path('docs/laravel-bridge-upgrade-plan.md'));

        foreach ([
            'facade/ignition 1.16.15',
            'nunomaduro/collision v3.2.0',
            'Symfony 4.4',
            'vlucas/phpdotenv v3.6.10',
            'dragonmantank/cron-expression',
            'ramsey/uuid',
            'dcat/laravel-admin',
            'dcat/easy-excel',
            'yansongda/pay',
        ] as $blocker) {
            $this->assertStringContainsString($blocker, $contents);
        }
    }

    public function test_bridge_plan_keeps_high_risk_changes_out_of_mainline(): void
    {
        $contents = file_get_contents(base_path('docs/laravel-bridge-upgrade-plan.md'));

        foreach ([
            '不恢复 PayPal / Stripe / Coinbase / Mapay / TokenPay / PayJS / Vpay / Paysapi',
            '必须删除 `config/admin.php` 或 `routes/admin/routes.php` 才能继续',
            '支付回调 URL 语义需要变更',
            '一次性跨 Laravel 7 / 8 直接进入 Laravel 10',
        ] as $guardrail) {
            $this->assertStringContainsString($guardrail, $contents);
        }
    }
}
