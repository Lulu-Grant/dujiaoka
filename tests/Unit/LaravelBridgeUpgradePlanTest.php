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
            'codex/php81-laravel8-upgrade` 已将升级分支推进到 Laravel 8.83',
            'sh scripts/composer81-docker check-platform-reqs',
            'sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml',
            'APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell',
            'OK (417 tests, 4292 assertions)',
            'Laravel 版本：`8.83.29`',
        ] as $needle) {
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_bridge_plan_records_laravel7_and_laravel8_blockers(): void
    {
        $contents = file_get_contents(base_path('docs/laravel-bridge-upgrade-plan.md'));

        foreach ([
            'nunomaduro/collision` 升级到 `^5.11`',
            'Symfony 组件链保持在可安装范围',
            'vlucas/phpdotenv` 升级到 5.6.x',
            'ramsey/uuid` 升级到 4.x',
            'psr/log` 固定为 `^1.1`',
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

    public function test_bridge_experiment_composer_constraints_are_laravel7_and_dual_runtime_safe(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertSame('^8.83', $composer['require']['laravel/framework']);
        $this->assertSame('^2.17', $composer['require-dev']['facade/ignition']);
        $this->assertSame('^5.11', $composer['require-dev']['nunomaduro/collision']);
        $this->assertSame('^4.7', $composer['require']['ramsey/uuid']);
        $this->assertSame('^1.1', $composer['require']['psr/log']);
        $this->assertSame('8.1.34', $composer['config']['platform']['php']);
        $this->assertSame('^2.5', $composer['require']['symfony/translation-contracts']);
    }
}
