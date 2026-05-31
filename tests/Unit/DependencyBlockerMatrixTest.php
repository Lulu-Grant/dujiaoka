<?php

namespace Tests\Unit;

use Tests\TestCase;

class DependencyBlockerMatrixTest extends TestCase
{
    public function test_dependency_matrix_tracks_current_direct_blockers(): void
    {
        $contents = file_get_contents(base_path('docs/dependency-blocker-matrix.md'));

        foreach ([
            'dcat/laravel-admin',
            'dcat/easy-excel',
            'yansongda/pay',
            'laravel/framework',
            '保留过渡',
            '后台登录、认证中间件、权限白名单和旧入口兼容',
            '官方支付宝 / 官方微信',
        ] as $needle) {
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_dependency_matrix_keeps_retired_payment_sdks_out_of_active_scope(): void
    {
        $contents = file_get_contents(base_path('docs/dependency-blocker-matrix.md'));
        $composer = file_get_contents(base_path('composer.json'));

        foreach ([
            'paypal/rest-api-sdk-php',
            'stripe/stripe-php',
            'xhat/payjs-laravel',
        ] as $package) {
            $this->assertStringContainsString($package, $contents);
            $this->assertStringNotContainsString('"'.$package.'"', $composer);
        }

        foreach ([
            'PayPal',
            'Stripe',
            'Coinbase',
            'Mapay',
            'TokenPay',
            'Paysapi',
            'Vpay',
            'PayJS',
        ] as $gateway) {
            $this->assertStringContainsString($gateway, $contents);
        }
    }

    public function test_dependency_matrix_keeps_beta2_experiment_commands(): void
    {
        $contents = file_get_contents(base_path('docs/dependency-blocker-matrix.md'));

        foreach ([
            './scripts/composer74 install --no-interaction --no-progress',
            './scripts/php74 artisan migrate:status',
            './scripts/php74 vendor/bin/phpunit',
            'ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell',
            'Composer version 2.2.25',
            'swiftmailer/swiftmailer',
            'symfony/debug',
        ] as $commandOrFinding) {
            $this->assertStringContainsString($commandOrFinding, $contents);
        }
    }

    public function test_dependency_matrix_marks_payment_callback_security_as_maintenance(): void
    {
        $contents = file_get_contents(base_path('docs/dependency-blocker-matrix.md'));

        $this->assertStringContainsString('回调异常路径已经有测试护栏', $contents);
        $this->assertStringContainsString('继续维护保留支付通道', $contents);
        $this->assertStringContainsString('异常不触发履约测试', $contents);
        $this->assertStringNotContainsString('继续补签名失败、金额不一致、重复通知、已完成订单重复推进测试', $contents);
    }
}
