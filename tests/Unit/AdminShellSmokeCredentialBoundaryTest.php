<?php

namespace Tests\Unit;

use Tests\TestCase;

class AdminShellSmokeCredentialBoundaryTest extends TestCase
{
    public function test_admin_shell_smoke_requires_explicit_credentials(): void
    {
        $script = file_get_contents(base_path('tests/Browser/admin-shell-smoke.sh'));

        $this->assertStringContainsString('ADMIN_USERNAME="${ADMIN_USERNAME:-}"', $script);
        $this->assertStringContainsString('ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"', $script);
        $this->assertStringContainsString('ADMIN_USERNAME and ADMIN_PASSWORD are required', $script);
        $this->assertStringNotContainsString('ADMIN_USERNAME="${ADMIN_USERNAME:-admin}"', $script);
        $this->assertStringNotContainsString('ADMIN_PASSWORD="${ADMIN_PASSWORD:-XiguaLocal@2026}"', $script);
    }

    public function test_local_docs_mark_smoke_credentials_as_development_only(): void
    {
        foreach ([
            'README.md',
            'docs/local-dev-quickstart.md',
            'docs/security-baseline-audit.md',
        ] as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            $this->assertStringContainsString('ADMIN_USERNAME', $contents, $relativePath);
            $this->assertStringContainsString('ADMIN_PASSWORD', $contents, $relativePath);
        }

        $readme = file_get_contents(base_path('README.md'));
        $quickstart = file_get_contents(base_path('docs/local-dev-quickstart.md'));
        $security = file_get_contents(base_path('docs/security-baseline-audit.md'));

        $this->assertStringContainsString('本地开发或测试专用后台账号', $readme);
        $this->assertStringContainsString('不是生产默认管理员', $quickstart);
        $this->assertStringContainsString('不再内置默认账号', $security);
    }
}
