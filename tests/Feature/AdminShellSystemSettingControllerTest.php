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

    public function test_base_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'manage_email' => 'admin@example.com',
            'order_expire_time' => 15,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/base');

        $response->assertOk();
        $response->assertSee('编辑基础站点配置');
        $response->assertSee('独角数卡西瓜版');
    }

    public function test_base_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/base', [
                'title' => '新的站点标题',
                'text_logo' => '新的文字 Logo',
                'template' => 'avatar',
                'language' => 'zh_CN',
                'manage_email' => 'owner@example.com',
                'order_expire_time' => 30,
                'keywords' => 'kw',
                'description' => 'desc',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/base');
        $response->assertSessionHas('status', '基础站点配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame('新的站点标题', $settings['title']);
        $this->assertSame('owner@example.com', $settings['manage_email']);
        $this->assertSame(30, $settings['order_expire_time']);
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
