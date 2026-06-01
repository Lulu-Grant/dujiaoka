<?php

namespace Tests\Unit;

use Tests\TestCase;

class ReleaseReadinessDocumentationTest extends TestCase
{
    public function test_release_documents_keep_required_validation_commands(): void
    {
        foreach ([
            'docs/releases/v3.0.0-beta.1.md',
            'docs/releases/v3.0.0-rc.md',
            'docs/releases/v3.0.0-rc.1.md',
            'docs/releases/v3.0.0-stable-readiness.md',
        ] as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            $this->assertStringContainsString('git diff --check', $contents, $relativePath);
            $this->assertStringContainsString('./scripts/php74 vendor/bin/phpunit', $contents, $relativePath);
            $this->assertStringContainsString('./scripts/smoke-admin-shell', $contents, $relativePath);
        }
    }

    public function test_release_documents_keep_freeze_boundaries_explicit(): void
    {
        $beta1 = file_get_contents(base_path('docs/releases/v3.0.0-beta.1.md'));
        $beta2 = file_get_contents(base_path('docs/releases/v3.0.0-beta.2.md'));
        $rc = file_get_contents(base_path('docs/releases/v3.0.0-rc.md'));
        $stable = file_get_contents(base_path('docs/releases/v3.0.0-stable-readiness.md'));

        $this->assertStringContainsString('不直接跳 Laravel / PHP 大版本', $beta1);
        $this->assertStringContainsString('不直接升级 Laravel / PHP 大版本', $beta2);
        $this->assertStringContainsString('不删除 Dcat 兼容关键文件', $beta2);
        $this->assertStringContainsString('后台壳主承载冻结', $rc);
        $this->assertStringContainsString('新高风险批量动作', $stable);
        $this->assertStringContainsString('删除 Dcat 兼容关键入口', $stable);
    }

    public function test_beta2_release_document_keeps_upgrade_experiment_commands_aligned(): void
    {
        $beta2 = file_get_contents(base_path('docs/releases/v3.0.0-beta.2.md'));

        foreach ([
            './scripts/composer74 install --no-interaction --no-progress',
            './scripts/php74 artisan migrate:status',
            './scripts/php74 vendor/bin/phpunit',
            'ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell',
        ] as $command) {
            $this->assertStringContainsString($command, $beta2);
        }
    }

    public function test_rc1_release_document_keeps_current_acceptance_status(): void
    {
        $rc1 = file_get_contents(base_path('docs/releases/v3.0.0-rc.1.md'));

        foreach ([
            '当前 RC.1 验收状态',
            '后台壳主承载',
            'Dcat 兼容层',
            '官方支付宝、官方微信、易支付、Epusdt',
            '退役通道防回流测试',
            '本地 smoke 凭据边界测试',
            '依赖阻塞矩阵',
            'OK (417 tests, 4281 assertions)',
            '远端 CI 已通过',
            'GitHub Actions `CI`',
        ] as $requiredStatus) {
            $this->assertStringContainsString($requiredStatus, $rc1);
        }
    }

    public function test_stable_readiness_document_keeps_current_state_and_legacy_boundaries(): void
    {
        $stable = file_get_contents(base_path('docs/releases/v3.0.0-stable-readiness.md'));

        foreach ([
            '当前 stable-ready 状态',
            'OK (417 tests, 4281 assertions)',
            '后台 smoke',
            'git diff --check',
            'CI',
            '远端 `master` 最新 GitHub Actions 结果为 success',
            '保留的遗留边界',
            'Laravel 7.30.7 桥接实验已通过',
            'Dcat Admin',
            'config/admin.php',
            'routes/admin/routes.php',
            'Yansongda Pay',
            'swiftmailer/swiftmailer',
            'symfony/debug',
            'PayPal、Stripe、Coinbase、Mapay、TokenPay、PayJS、Vpay、Paysapi 不恢复',
            './scripts/composer74 install --no-interaction --no-progress',
        ] as $requiredStatus) {
            $this->assertStringContainsString($requiredStatus, $stable);
        }
    }

    public function test_release_and_security_documents_do_not_contain_real_secret_shapes(): void
    {
        foreach ([
            'README.md',
            'docs/security-baseline-audit.md',
            'docs/releases/v3.0.0-beta.1.md',
            'docs/releases/v3.0.0-beta.2.md',
            'docs/releases/v3.0.0-rc.md',
            'docs/releases/v3.0.0-rc.1.md',
            'docs/releases/v3.0.0-stable-readiness.md',
            '.env.example',
            '.env.local.example',
        ] as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            $this->assertDoesNotMatchRegularExpression('/APP_KEY=base64:[A-Za-z0-9+\/=]{20,}/', $contents, $relativePath);
            $this->assertDoesNotMatchRegularExpression('/sk_live_[A-Za-z0-9]{16,}/', $contents, $relativePath);
            $this->assertDoesNotMatchRegularExpression('/rk_live_[A-Za-z0-9]{16,}/', $contents, $relativePath);
            $this->assertDoesNotMatchRegularExpression('/whsec_[A-Za-z0-9]{16,}/', $contents, $relativePath);
        }
    }
}
