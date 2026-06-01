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
            'codex/laravel7-bridge-experiment` 已将实验分支推进到 Laravel 7.30.7',
            'sh scripts/composer81-docker check-platform-reqs',
            'sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml',
            'APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell',
            'OK (417 tests, 4281 assertions)',
            'Laravel 版本：`7.30.7`',
        ] as $needle) {
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_bridge_plan_records_laravel7_and_laravel8_blockers(): void
    {
        $contents = file_get_contents(base_path('docs/laravel-bridge-upgrade-plan.md'));

        foreach ([
            'facade/ignition` 升级到 `^2.17`',
            'nunomaduro/collision` 升级到 `^4.3`',
            'Symfony 组件链进入 5.4',
            'vlucas/phpdotenv` 升级到 4.x',
            'ramsey/uuid` 固定在 3.9 线',
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

        $this->assertSame('^7.30', $composer['require']['laravel/framework']);
        $this->assertSame('^2.17', $composer['require-dev']['facade/ignition']);
        $this->assertSame('^4.3', $composer['require-dev']['nunomaduro/collision']);
        $this->assertSame('^3.9', $composer['require']['ramsey/uuid']);
        $this->assertSame('^1.1', $composer['require']['psr/log']);
        $this->assertSame('7.4.33', $composer['config']['platform']['php']);
        $this->assertSame('^2.5', $composer['require']['symfony/translation-contracts']);
    }
}
