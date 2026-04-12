<?php

namespace Tests\Feature;

use App\Service\SystemSettingService;
use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellEmailTestControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_email_test_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'from_address' => 'bot@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/email-test');

        $response->assertOk();
        $response->assertSee('邮件测试概览');
        $response->assertSee('测试邮件表单合同');
        $response->assertSee('当前邮件运行时配置');
        $response->assertSee('进入旧版功能页');
        $response->assertSee('/admin/email-test');
    }

    public function test_show_renders_email_test_detail_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'from_address' => 'bot@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/email-test/2');

        $response->assertOk();
        $response->assertSee('邮件测试详情');
        $response->assertSee('邮件驱动');
        $response->assertSee('smtp.example.com');
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'admin-shell-tester'],
            [
                'password' => bcrypt('secret123'),
                'name' => 'Admin Shell Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return Administrator::query()->where('username', 'admin-shell-tester')->firstOrFail();
    }
}
