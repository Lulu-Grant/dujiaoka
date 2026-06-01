<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminShell\AuthShellController;
use Dcat\Admin\Models\Administrator;
use Tests\TestCase;

class DcatCompatibilityLayerAuditTest extends TestCase
{
    public function test_dcat_audit_records_laravel7_compatibility_responsibilities(): void
    {
        $contents = file_get_contents(base_path('docs/dcat-compatibility-layer-audit.md'));

        foreach ([
            'Laravel 7.30.7',
            '后台登录',
            '认证 guard / provider',
            '中间件',
            '权限白名单',
            '旧 `/admin/*` 到 `/admin/v2/*` query-preserving 跳转',
            '不删除 `config/admin.php`',
            '不删除 `routes/admin/routes.php`',
            '不新增 Dcat 绑定型后台能力',
        ] as $needle) {
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_dcat_runtime_config_is_limited_to_compatibility_layer(): void
    {
        $this->assertSame('admin', config('admin.route.prefix'));
        $this->assertSame(['web', 'admin'], config('admin.route.middleware'));
        $this->assertSame(base_path('routes/admin'), config('admin.directory'));
        $this->assertSame(AuthShellController::class, config('admin.auth.controller'));
        $this->assertSame('admin', config('admin.auth.guard'));
        $this->assertSame(Administrator::class, config('admin.auth.providers.admin.model'));

        $permissionExcept = config('admin.permission.except');

        foreach ([
            '/',
            'v2/dashboard',
            'auth/login',
            'auth/logout',
            'auth/setting',
        ] as $path) {
            $this->assertContains($path, $permissionExcept);
        }
    }
}
