<?php

namespace Tests\Feature;

use App\Service\SystemSettingService;
use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellSystemSettingControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_system_setting_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting');

        $response->assertOk();
        $response->assertSee('系统设置概览');
        $response->assertSee('基础站点配置');
        $response->assertSee('邮件发送配置');
        $response->assertSee('进入旧版功能页');
        $response->assertSee('/admin/system-setting');
    }

    public function test_show_renders_system_setting_detail_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'from_address' => 'hello@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/3');

        $response->assertOk();
        $response->assertSee('系统设置详情');
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
